using System;
using System.Collections.Generic;
using System.Text;
using IRCeX;
using System.Xml;
using MySql.Data.MySqlClient;

namespace IWDB {
    
	public delegate void NeueFlottenGesichtetDelegate(String zielName, int Anzahl, bool angriff);
	public delegate void NeueKbGesichtet(uint gala, uint sys, uint pla, String ownerName, String ownerAlly);
	public interface IWDBCore {
		void CheckLogin(string nick, string username, string host);
		void CheckLogout(string nick, string username, string host);
        void CheckUsers(string p, List<string> checkingUsers);
        event NeueFlottenGesichtetDelegate OnNeueFlottenGesichtet;
		event NeueKbGesichtet OnNeueKbGesichtet;
		void BauleerlaufInfo(out int Anzahl, out List<Pair<int, String>> neuerLeerlauf);
        void SitterauftraegeOffen(out int offeneAuftraege, out int firstJobId, out DateTime naechsterAuftrag);
        void AnfliegendeFlotten(out uint flottenAnz, out uint zielplaniAnz);
        void LogEvent(String evt);
        List<PlaniData> PlanisMitBesitzer(String name);
        List<PlaniData> PlanisInSystem(uint gala, uint sys);
    }

	//momentan offene Aufträge jede Minute spammen - bei Änderung nicht
	//Wenn von 0 auf 1 offene Aufträge => zu dem Zeitpunkt spammen wo der Auftrag offen wird
	public class IWDBChanModule:IIRCChanModule {
		public const int BauleerlaufSpamIntervalInSeconds = 300;
        public const String SitterSpamColor = "\u00031,7";
        public const String FlottenSpamColor = "\u00031,11";

		IIRCModuleChan chan;
		XmlNode config;
		String conStr;
		String DBPrefix;
		IWDBCore iwdb;
		String baseUrl;

		List<String> joinedUsers = new List<string>();
        TimerEvent sitterSpamEvent;
        TimerEvent bauLeerlaufSpamEvent;
		Dictionary<String, String> objektKürzel;
		Dictionary<String, String> planiKürzel;

        static Dictionary<String, String> ircColors;
        static IWDBChanModule() {
            ircColors = new Dictionary<string, string>();
            ircColors.Add("0", "white");
            ircColors.Add("1", "black");
            ircColors.Add("2", "navy");
            ircColors.Add("3", "green");
            ircColors.Add("4", "red");
            ircColors.Add("5", "maroon");
            ircColors.Add("6", "purple");
            ircColors.Add("7", "orange");
            ircColors.Add("8", "yellow");
            ircColors.Add("9", "lime");
            ircColors.Add("10", "teal");
            ircColors.Add("11", "cyan");
            ircColors.Add("12", "royal");
            ircColors.Add("13", "fuchsia");
            ircColors.Add("14", "grey");
            ircColors.Add("15", "silver");
            for (int i = 0; i < 16; ++i) {
                ircColors.Add("," + i.ToString(), ircColors[i.ToString()]);
            }
        }

		internal IWDBChanModule() {
			objektKürzel = new Dictionary<string, string>();
			objektKürzel.Add("", "FEHLER"); //Kann nur auftreten wenn beim Einfügen des Planis in die Datenbank etwas schief gegangen ist
			objektKürzel.Add("---", "---");
            objektKürzel.Add("Kolonie", "Kolo");
			objektKürzel.Add("Sammelbasis", "SaBa");
			objektKürzel.Add("Kampfbasis", "KaBa");
			objektKürzel.Add("Raumstation", "RS");
			planiKürzel = new Dictionary<string, string>();
			planiKürzel.Add("", "FEHLER"); //Kann nur auftreten wenn beim Einfügen des Planis in die Datenbank etwas schief gegangen ist
			planiKürzel.Add("Nichts", "---");
			planiKürzel.Add("Steinklumpen", "Ste");
			planiKürzel.Add("Gasgigant", "Gas");
			planiKürzel.Add("Asteroid", "Ast");
			planiKürzel.Add("Eisplanet", "Eis");
			planiKürzel.Add("Stargate", "Stg");
			planiKürzel.Add("Elektrosturm", "Ele");
			planiKürzel.Add("Raumverzerrung", "Rvz");
			planiKürzel.Add("Ionensturm", "Ion");
			planiKürzel.Add("grav. Anomalie", "gAn");
		}

        #region IIRCChanModule Member
        public bool Disable() {
			iwdb.OnNeueFlottenGesichtet -= NeueFlottenEntdecktCallback;
			iwdb.OnNeueKbGesichtet -= NeueKabaEntdecktCallback;
			chan.OnUserJoin -= OnUserJoin;
			chan.OnUserLeave -= OnUserLeave;
			chan.OnChannelJoined -= OnChannelJoined;
			chan.UnregisterCmd(this, ".sitter");
			chan.UnregisterCmd(this, ".flotten");
			chan.UnregisterCmd(this, ".owner");
			chan.UnregisterCmd(this, ".sys");
            chan.UnregisterCmd(this, ".status");
            chan.UnregisterCmd(this, ".bauleerlauf");
			chan.UnregisterCmd(this, ".tt");
			sitterSpamEvent.Disable();
            bauLeerlaufSpamEvent.Disable();
			return true;
		}

		public bool Enable() {
            ConfigUtils.SetDefaultElementValue(config, "mysql", "");
            ConfigUtils.SetDefaultElementValue(config, "dbprefix", "");
			ConfigUtils.SetDefaultElementValue(config, "baseurl", "https://www.ancient-empires.de/schaftool/");
            DBPrefix = config["dbprefix"].InnerText;
			baseUrl = config["baseurl"].InnerText;
            IWDBParserManager parserMan = (IWDBParserManager)chan.getUtil("iwdb");
            try {
                iwdb = parserMan.GetCore(config["name"].InnerText);
            } catch (KeyNotFoundException) {
                Log.WriteLine(LogLevel.E_ERROR, "IWDBChanModule@" + chan.Name + ": iwdbcore nicht gefunden!");
                return false;
            }
			iwdb.OnNeueFlottenGesichtet += NeueFlottenEntdecktCallback;
			iwdb.OnNeueKbGesichtet += NeueKabaEntdecktCallback;
			//mysql = chan.MysqlConnections.GetConnection(config["mysql"].InnerText);
			conStr = config["mysql"].InnerText;
			MySqlConnection con = chan.AllocateConnection(this, conStr);
			chan.FreeConnection(this, con);
			chan.RegisterCmd(this, ".sitter", CmdSitter, IRCModuleUserAccess.Normal, "Sitterspam!");
			chan.RegisterCmd(this, ".flotten", CmdFlotten, IRCModuleUserAccess.Normal, "Flottenspam!");
			chan.RegisterCmd(this, ".owner", CmdOwner, IRCModuleUserAccess.Normal, "<Besitzer> - Planisuche nach Besitzer");
			chan.RegisterCmd(this, ".sys", CmdSystem, IRCModuleUserAccess.Normal, "<gala>:<sys> - Listet alle Planis in dem System auf");
            chan.RegisterCmd(this, ".status", CmdStatus, IRCModuleUserAccess.Normal, "Statusspam!");
            chan.RegisterCmd(this, ".bauleerlauf", CmdBauleerlauf, IRCModuleUserAccess.Normal);
			chan.RegisterCmd(this, ".tt", CmdTimeTest, IRCModuleUserAccess.Mod, "TimeTest");
            chan.OnUserJoin += OnUserJoin;
			chan.OnUserLeave += OnUserLeave;
			chan.OnChannelJoined += OnChannelJoined;
			sitterSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), this, SitterSpamEventCallback, null);
            bauLeerlaufSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), this, BauLeerlaufSpamCallback, null);
			return true;
		}

		public bool ForceDisable() {
			return Disable();
		}

		public string Name {
			get { return "iwdbspam"; }
		}

		public void Registered(IIRCModuleChan Chan, XmlNode Config) {
			this.chan = Chan;
			this.config = Config;
		}
#endregion

        public static String IrcToHtml(String msg) {
            StringBuilder sb = new StringBuilder();
            msg = IRCeX.ConfigUtils.XmlEscape(msg);
            msg = System.Text.RegularExpressions.Regex.Replace(msg, "\u0003(\\d{1,2})?(,\\d{1,2})?", m => !m.Groups[1].Success ? "</span>" : "<span style=\"color: " + ircColors[m.Groups[1].Value] + (m.Groups[2].Success ? ";background-color:" + ircColors[m.Groups[2].Value] : "") + ";\">");
            bool bold=false;
            bool underline=false;

            foreach (char c in msg) {
                switch (c) {
                    case IrcFormat.Bold:
                        sb.Append(bold ? "</b>" : "<b>");
                        bold = !bold;
                        break;
                    case IrcFormat.Underline:
                        sb.Append(underline ? "</span>" : "<span style=\"text-decoration:underline;\">");
                        underline = !underline;
                        break;
                    case IrcFormat.Reverse:
                        //Nicht unterstützt, aber zumindest ignoriert
                        break;
                    default:
                        sb.Append(c);
                        break;
                }
            }
            if (bold)
                sb.Append("</b>");
            if (underline)
                sb.Append("</span>");
            String str = sb.ToString();
            for (int i = str.Replace("</span", "</spa").Length - str.Replace("<span", "<spa").Length; i > 0; i--) {
                sb.Append("</span>");
            }
            return sb.ToString();
        }

        protected void LogEvent(String msg) {
            iwdb.LogEvent(IrcToHtml(msg));
        }

		void OnUserJoin(string nick, string username, string host) {
			iwdb.CheckLogin(nick, username, host);
			if (!chan.UserHasAccess(nick, IRCModuleUserAccess.Normal)) {
				chan.Kick(nick, "Deine Hostmaske wurde nicht erkannt, bitte im Tool in den Einstellungen eintragen!");
				return;
			}
			joinedUsers.Add(nick);
			if (joinedUsers.Count == 1)
				chan.SetTimerEvent(DateTime.Now.AddSeconds(3), this, SendGreetingMessages, null);
		}
		void OnUserLeave(string nick, string username, string host) {
            Log.WriteLine(nick + " has Left Channel (IWDBChanModule.OnUserLeave)");
			iwdb.CheckLogout(nick, username, host);
		}
		void OnChannelJoined(object Sender, EventArgs args) {
			chan.SetTimerEvent(DateTime.Now.AddSeconds(5), this, CheckChan, null);
		}
		void SendGreetingMessages(object timerIdentifyer) {
            int offeneAuftraege, jobid;
            DateTime naechsterAuftrag;
            iwdb.SitterauftraegeOffen(out offeneAuftraege, out jobid, out naechsterAuftrag);
            StringBuilder sb = new StringBuilder("Es sind momentan ");
            sb.Append(offeneAuftraege);
            sb.Append(" Sitteraufträge offen! ");
            if(offeneAuftraege>0) {
				sb.Append(baseUrl);
                sb.Append("index.php?action=sitter_login&from=sitter_view&jobid=");
                sb.Append(jobid);
            }
            SendGreetingMessage(joinedUsers, sb.ToString());
			if (naechsterAuftrag != DateTime.MinValue) {
                TimeSpan s = naechsterAuftrag - DateTime.Now;
                SendGreetingMessage(joinedUsers, "Der nächste Sitterauftrag ist um " + naechsterAuftrag.ToString("HH:mm:ss") + " (in " + s.ToString("hh\\:mm\\:ss") + ")");
			}
			joinedUsers.Clear();
		}
		void SendGreetingMessage(List<string> targets, String message) {
			if (targets.Count >= 3) {
				chan.SendChanMsg(message);
			} else {
				foreach (String target in targets) {
					chan.SendUserNotice(target, message);
				}
			}
		}

		void CheckChan(object timerIdentifyer) {
            List<String> checkingUsers = new List<string>();
			foreach (IRCChanMember member in chan.Members) {
                if (member.Name != chan.OwnNick && member.Name != "ChanServ")
                    checkingUsers.Add(member.Name);
			}
            iwdb.CheckUsers(chan.Name, checkingUsers);
		}

		void CmdSitter(IRCModuleMessage Msg) {
			SitterSpam(true);
		}
		void CmdFlotten(IRCModuleMessage msg) {
			FlottenSpam(true);
		}
		void CmdOwner(IRCModuleMessage Msg) {
            String args = Msg.Args.Trim();
            if (args.Length == 0) {
                Msg.AnswerQuiet("Fehlerhafte Benutzung des .owner-Kommandos. Richtig ist: .owner <Namensteil>");
                return;
            }
            List<PlaniData> planis = iwdb.PlanisMitBesitzer(args);
            foreach (PlaniData p in planis) {
                SendSystemResult(p);
            }
		}
		void CmdSystem(IRCModuleMessage Msg) {
			String[] args = Msg.Args.Trim().Split(new char[] { ':' }, 2);
			if (args.Length != 2 || args[0].Length==0 || args[1].Length==0) {
				Msg.AnswerQuiet("Fehlerhafte Benutzung des .sys-Kommandos! Richtig ist .sys <gala>:<system>!");
				return;
			}
			uint gala, sys;
			try {
				gala = uint.Parse(args[0]);
				sys = uint.Parse(args[1]);
			} catch (FormatException) {
				Msg.AnswerQuiet("Fehlerhafte Benutzung des .sys-Kommandos! Richtig ist .sys <gala>:<system>!");
				return;
			}
            List<PlaniData> planis = iwdb.PlanisInSystem(gala, sys);
            foreach (PlaniData p in planis) {
                SendSystemResult(p);
            }
		}
        void CmdStatus(IRCModuleMessage Msg) {
            joinedUsers.Add(Msg.SenderNick);
            if (joinedUsers.Count == 1)
                chan.SetTimerEvent(DateTime.Now.AddSeconds(3), this, SendGreetingMessages, null);
        }
        void CmdBauleerlauf(IRCModuleMessage Msg) {
            BauLeerlaufSpam(true);
        }
		void CmdTimeTest(IRCModuleMessage Msg) {
			chan.SendChanMsg("Now:" + DateTime.Now.ToString());
			uint timestamp = IWDBUtils.toUnixTimestamp(DateTime.Now);
			uint utcTimestamp = IWDBUtils.toUnixTimestamp(DateTime.UtcNow);
			chan.SendChanMsg("Timestamps: " + timestamp + " UTC: " + utcTimestamp);
			chan.SendChanMsg("Back: " + IWDBUtils.fromUnixTimestamp(timestamp) + " UTC: " + IWDBUtils.fromUnixTimestamp(utcTimestamp));
		}
		
		private void SendSystemResult(PlaniData dta) {
            StringBuilder sb = new StringBuilder(dta.Gala.ToString());
			sb.Append(':');
			sb.Append(dta.Sys);
			sb.Append(':');
			sb.Append(dta.Pla);
			sb.Append(' ');
            sb.Append(objektKürzel.GetOrDefault(dta.Objekttyp, "Fehler"));
			sb.Append('@');
            sb.Append(planiKürzel.GetOrDefault(dta.Planityp, "Fehler"));
			//sb.Append(dta.Planityp);
			sb.Append(" \"");
			sb.Append(dta.Planiname);
			sb.Append("\" von ");
			sb.Append(dta.Ownername);
			if (dta.Ownerally.Length>0) {
				sb.Append('[');
				sb.Append(dta.Ownerally);
				sb.Append(']');
			}
			chan.SendDelayedChanMsg(sb.ToString());
		}

		void SitterSpamEventCallback(object timerIdentifyer) {
			SitterSpam(false);
			FlottenSpam(false);
			sitterSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), this, SitterSpamEventCallback, null);
		}
        void BauLeerlaufSpamCallback(object timerIdentifyer) {
            BauLeerlaufSpam(false);
			bauLeerlaufSpamEvent = chan.SetTimerEvent(DateTime.Now.AddSeconds(BauleerlaufSpamIntervalInSeconds), this, BauLeerlaufSpamCallback, null);
        }

        void BauLeerlaufSpam(bool verbose) {
            int anzahl;
			List<Pair<int, String>> neuerLeerlauf;
            iwdb.BauleerlaufInfo(out anzahl, out neuerLeerlauf);
			foreach(Pair<int, String> igmAccount in neuerLeerlauf) {
                String msg = igmAccount.Item2 + " hat jetzt Leerlauf! " + baseUrl + "index.php?action=sitter_login&from=sitter_list&id=" + igmAccount.Item1;
                LogEvent(msg);
				chan.SendDelayedChanMsg(msg);
            }
            if (anzahl > 0 || verbose) {
                LogEvent(anzahl + " Leute haben momentan Bau- oder Forschungsleerlauf!");
                chan.SendChanMsg(anzahl + " Leute haben momentan Bau- oder Forschungsleerlauf!");
            }

        }
		void SitterSpam(bool verbose) {
            int offen, jobid;
            DateTime naechster;
            iwdb.SitterauftraegeOffen(out offen, out jobid, out naechster);
            if (verbose || offen > 0)
                if (offen == 0) {
                    chan.SendChanMsg("Es sind momentan 0 Sitteraufträge offen!");
                } else {
                    String msg = SitterSpamColor + "Es sind momentan " + offen + " Sitteraufträge offen! " + baseUrl + "index.php?action=sitter_login&from=sitter_view&jobid=" + jobid;
                    LogEvent(msg);
					chan.SendChanMsg(msg);
                }
            if (verbose && naechster!=DateTime.MinValue) {
                TimeSpan s = naechster - DateTime.Now;
				chan.SendChanMsg("Der nächste Sitterauftrag ist um " + naechster.ToString("HH:mm:ss") + " (in " + s.ToString("hh\\:mm\\:ss") + ")");
            }
		}
		void FlottenSpam(bool verbose) {
            uint flottenAnz, zielPlaniAnz;
            iwdb.AnfliegendeFlotten(out flottenAnz, out zielPlaniAnz);
            if (flottenAnz > 0 || verbose) {
                String msg = FlottenSpamColor + "Innerhalb der nächsten 5 Minuten kommen " + flottenAnz + " Flotten bei " + zielPlaniAnz + " verschiedenen Zielplanis an!";
                LogEvent(msg);
                chan.SendChanMsg(msg);
            }
		}
		void NeueFlottenEntdecktCallback(String spieler, int anz, bool angriff) {
            if (angriff) {
                String msg = FlottenSpamColor + IrcFormat.Bold + "ANGRIFF " + IrcFormat.Bold + " auf " + spieler + " - " + anz + " neue angreifende Flotten!";
                LogEvent(msg);
                chan.SendChanMsg(msg);
            } else {
                String msg = FlottenSpamColor + IrcFormat.Bold + "SCAN" + IrcFormat.Bold + " auf " + spieler + " - " + anz + " neue Scans!";
                LogEvent(msg);
                chan.SendChanMsg(msg);
            }
		}
		void NeueKabaEntdecktCallback(uint gala, uint sys, uint pla, string ownerName, string ownerAlly) {
            String msg = FlottenSpamColor + IrcFormat.Bold + "KABA" + IrcFormat.Bold + " " + gala + ":" + sys + ":" + pla + " von " + ownerName + (ownerAlly != null ? "[" + ownerAlly + "]" : "");
            LogEvent(msg);
			chan.SendChanMsg(msg);
		}
	}
	public class IWDBChanModuleFactory:IIRCChanModuleFactory {
		#region IIRCChanModuleFactory Member

		public string ModuleName {
			get { return "iwdbspam"; }
		}

		public IIRCChanModule createInstance() {
			return new IWDBChanModule();
		}

		#endregion
	}
}
