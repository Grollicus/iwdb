using System;
using System.Collections.Generic;
using System.Text;
using IRCeX;
using System.Xml;
using MySql.Data.MySqlClient;

namespace IWDB {
    
	public delegate void NeueFlottenGesichtetDelegate(String zielName, int Anzahl);
	public interface IWDBCore {
		void CheckLogin(string nick, string username, string host);
		void CheckLogout(string nick, string username, string host);
        void CheckUsers(string p, List<string> checkingUsers);
        event NeueFlottenGesichtetDelegate OnNeueFeindlFlottenGesichtet;
        void BauleerlaufInfo(out int Anzahl, out List<String> neuerLeerlauf);
        void SitterauftraegeOffen(out int offeneAuftraege, out int firstJobId, out DateTime naechsterAuftrag);
        void AnfliegendeFlotten(out uint flottenAnz, out uint zielplaniAnz);
        List<PlaniData> PlanisMitBesitzer(String name);
        List<PlaniData> PlanisInSystem(uint gala, uint sys);
    }

	//momentan offene Auftr�ge jede Minute spammen - bei �nderung nicht
	//Wenn von 0 auf 1 offene Auftr�ge => zu dem Zeitpunkt spammen wo der Auftrag offen wird
	public class IWDBChanModule:IIRCChanModule {
		public const int BauleerlaufSpamIntervalInSeconds = 300;
        public const String SitterSpamColor = "\u00031,7";
        public const String FlottenSpamColor = "\u00031,11";

		IRCModuleChan chan;
		XmlNode config;
		MySqlConnection mysql;
		String DBPrefix;
		IWDBCore iwdb;

		List<String> joinedUsers = new List<string>();
        TimerEvent sitterSpamEvent;
        TimerEvent bauLeerlaufSpamEvent;
		Dictionary<String, String> objektK�rzel;
		Dictionary<String, String> planiK�rzel;

		internal IWDBChanModule() {
			objektK�rzel = new Dictionary<string, string>();
			objektK�rzel.Add("", "FEHLER"); //Kann nur auftreten wenn beim Einf�gen des Planis in die Datenbank etwas schief gegangen ist
			objektK�rzel.Add("---", "---");
            objektK�rzel.Add("Kolonie", "Kolo");
			objektK�rzel.Add("Sammelbasis", "SaBa");
			objektK�rzel.Add("Kampfbasis", "KaBa");
			objektK�rzel.Add("Raumstation", "RS");
			planiK�rzel = new Dictionary<string, string>();
			planiK�rzel.Add("", "FEHLER"); //Kann nur auftreten wenn beim Einf�gen des Planis in die Datenbank etwas schief gegangen ist
			planiK�rzel.Add("Nichts", "---");
            planiK�rzel.Add("Steinklumpen", "Ste");
			planiK�rzel.Add("Gasgigant", "Gas");
			planiK�rzel.Add("Asteroid", "Ast");
			planiK�rzel.Add("Eisplanet", "Eis");
			planiK�rzel.Add("Stargate", "Stg");
			planiK�rzel.Add("Elektrosturm", "Ele");
			planiK�rzel.Add("Raumverzerrung", "Rvz");
			planiK�rzel.Add("Ionensturm", "Ion");
			planiK�rzel.Add("grav. Anomalie", "gAn");
		}

#region IIRCChanModule Member
		public bool Disable() {
			iwdb.OnNeueFeindlFlottenGesichtet -= NeueFeindFlottenEntdecktCallback;
			chan.OnUserJoin -= OnUserJoin;
			chan.OnUserLeave -= OnUserLeave;
			chan.OnChannelJoined -= OnChannelJoined;
			chan.UnregisterCmd(this, ".sitter");
			chan.UnregisterCmd(this, ".owner");
			chan.UnregisterCmd(this, ".sys");
            chan.UnregisterCmd(this, ".status");
            chan.UnregisterCmd(this, ".bauleerlauf");
			sitterSpamEvent.Disable();
            bauLeerlaufSpamEvent.Disable();
			return true;
		}

		public bool Enable() {
            ConfigUtils.SetDefaultElementValue(config, "mysql", "");
            ConfigUtils.SetDefaultElementValue(config, "dbprefix", "");
            DBPrefix = config["dbprefix"].InnerText;
            IWDBParserManager parserMan = (IWDBParserManager)chan.getUtil("iwdb");
            try {
                iwdb = parserMan.GetCore(config["name"].InnerText);
            } catch (KeyNotFoundException) {
                Log.WriteLine(LogLevel.E_ERROR, "IWDBChanModule@" + chan.Name + ": iwdbcore nicht gefunden!");
                return false;
            }
			iwdb.OnNeueFeindlFlottenGesichtet += NeueFeindFlottenEntdecktCallback;
			mysql = chan.MysqlConnections.GetConnection(config["mysql"].InnerText);
			chan.RegisterCmd(this, ".sitter", CmdSitter, IRCModuleUserAccess.Normal, "Sitterspam!");
			chan.RegisterCmd(this, ".owner", CmdOwner, IRCModuleUserAccess.Normal, "<Besitzer> - Planisuche nach Besitzer");
			chan.RegisterCmd(this, ".sys", CmdSystem, IRCModuleUserAccess.Normal, "<gala>:<sys> - Listet alle Planis in dem System auf");
            chan.RegisterCmd(this, ".status", CmdStatus, IRCModuleUserAccess.Normal, "Statusspam!");
            chan.RegisterCmd(this, ".bauleerlauf", CmdBauleerlauf, IRCModuleUserAccess.Normal);
            chan.OnUserJoin += OnUserJoin;
			chan.OnUserLeave += OnUserLeave;
			chan.OnChannelJoined += OnChannelJoined;
			sitterSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), SitterSpamEventCallback, null);
            bauLeerlaufSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), BauLeerlaufSpamCallback, null);
			return true;
		}

		public bool ForceDisable() {
			if (mysql.State == System.Data.ConnectionState.Open)
				mysql.Close();
			return Disable();
		}

		public string Name {
			get { return "iwdbspam"; }
		}

		public void Registered(IRCModuleChan Chan, XmlNode Config) {
			this.chan = Chan;
			this.config = Config;
		}
#endregion

		void OnUserJoin(string nick, string username, string host) {
			iwdb.CheckLogin(nick, username, host);
			if (!chan.UserHasAccess(nick, IRCModuleUserAccess.Normal)) {
				chan.Kick(nick, "Deine Hostmaske wurde nicht erkannt, bitte im Tool in den Einstellungen eintragen!");
				return;
			}
			joinedUsers.Add(nick);
			if (joinedUsers.Count == 1)
				chan.SetTimerEvent(DateTime.Now.AddSeconds(3), SendGreetingMessages, null);
		}
		void OnUserLeave(string nick, string username, string host) {
            Log.WriteLine(nick + " has Left Channel (IWDBChanModule.OnUserLeave)");
			iwdb.CheckLogout(nick, username, host);
		}
		void OnChannelJoined(object Sender, EventArgs args) {
			chan.SetTimerEvent(DateTime.Now.AddSeconds(5), CheckChan, null);
		}
		void SendGreetingMessages(object timerIdentifyer) {
			SendGreetingMessage(joinedUsers, "Hallo!");

            int offeneAuftraege, jobid;
            DateTime naechsterAuftrag;
            iwdb.SitterauftraegeOffen(out offeneAuftraege, out jobid, out naechsterAuftrag);
            StringBuilder sb = new StringBuilder("Es sind momentan ");
            sb.Append(offeneAuftraege);
            sb.Append(" Sitterauftr�ge offen! ");
            if(offeneAuftraege>0) {
                sb.Append("https://www.ancient-empires.de/tool/index.php?action=sitter_login&from=sitter_view&jobid=");
                sb.Append(jobid);
            }
            SendGreetingMessage(joinedUsers, sb.ToString());
			if (naechsterAuftrag != DateTime.MinValue) {
                TimeSpan s = naechsterAuftrag - DateTime.Now;
                SendGreetingMessage(joinedUsers, "Der n�chste Sitterauftrag ist um " + naechsterAuftrag.ToShortTimeString() + " (in " + s.ToString() + ")");
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
                if (member.Name != chan.OwnNick)
                    checkingUsers.Add(member.Name);
			}
            iwdb.CheckUsers(chan.Name, checkingUsers);
		}

		void CmdSitter(IRCModuleMessage Msg) {
			SitterSpam(true);
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
                chan.SetTimerEvent(DateTime.Now.AddSeconds(3), SendGreetingMessages, null);
        }
        void CmdBauleerlauf(IRCModuleMessage Msg) {
            BauLeerlaufSpam(true);
        }
		
		private void SendSystemResult(PlaniData dta) {
            StringBuilder sb = new StringBuilder(dta.Gala);
			sb.Append(':');
			sb.Append(dta.Sys);
			sb.Append(':');
			sb.Append(dta.Pla);
			sb.Append(' ');
            sb.Append(IWDBUtils.Get<string, string>(objektK�rzel, dta.Objekttyp, "Fehler!"));
			sb.Append('@');
			//sb.Append(planiK�rzel[dta.Planityp]);
            sb.Append(IWDBUtils.Get<string, string>(planiK�rzel, dta.Planityp, "Fehler!"));
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
			sitterSpamEvent = chan.SetTimerEvent(DateTime.Now.AddMinutes(1), SitterSpamEventCallback, null);
		}
        void BauLeerlaufSpamCallback(object timerIdentifyer) {
            BauLeerlaufSpam(false);
			bauLeerlaufSpamEvent = chan.SetTimerEvent(DateTime.Now.AddSeconds(BauleerlaufSpamIntervalInSeconds), BauLeerlaufSpamCallback, null);
        }

        void BauLeerlaufSpam(bool verbose) {
            int anzahl;
            List<String> neuerLeerlauf;
            iwdb.BauleerlaufInfo(out anzahl, out neuerLeerlauf);
            foreach(String name in neuerLeerlauf) {
                chan.SendDelayedChanMsg(name+ " hat jetzt Leerlauf!");
            }
            if (anzahl > 0 || verbose) {
                chan.SendChanMsg(anzahl + " Leute haben momentan Bau- oder Forschungsleerlauf!");
            }

        }
		void SitterSpam(bool verbose) {
            int offen, jobid;
            DateTime naechster;
            iwdb.SitterauftraegeOffen(out offen, out jobid, out naechster);
            if (verbose || offen > 0)
                if (offen == 0) {
                    chan.SendChanMsg("Es sind momentan 0 Sitterauftr�ge offen!");
                } else {
                    chan.SendChanMsg(SitterSpamColor+"Es sind momentan " + offen + " Sitterauftr�ge offen! https://www.ancient-empires.de/tool/index.php?action=sitter_login&from=sitter_view&jobid=" + jobid);
                }
            if (verbose && naechster!=DateTime.MinValue) {
                TimeSpan s = naechster - DateTime.Now;
                chan.SendChanMsg("Der n�chste Sitterauftrag ist um " + naechster.ToShortTimeString() + "( in " + s.ToString() + ")");
            }
		}
		void FlottenSpam(bool verbose) {
            uint flottenAnz, zielPlaniAnz;
            iwdb.AnfliegendeFlotten(out flottenAnz, out zielPlaniAnz);
            if (flottenAnz > 0 || verbose)
                chan.SendChanMsg(FlottenSpamColor+"Innerhalb der n�chsten 5 Minuten kommen " + flottenAnz + " Flotten bei " + zielPlaniAnz + " verschiedenen Zielplanis an!");

		}
		void NeueFeindFlottenEntdecktCallback(String spieler, int anz) {
            chan.SendChanMsg(SitterSpamColor + "Bei " + spieler + " wurden " + anz + " neue angreifende Flotten entdeckt!");
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
