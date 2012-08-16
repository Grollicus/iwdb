using System;
using System.Collections.Generic;
using System.Text;
using System.Net.Sockets;
using System.Net;
using System.IO;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using IRCeX;
using System.Xml;
using System.Threading;

namespace IWDB {
    public interface IWDBParserManager {
        IWDBCore GetCore(String name);
    }
    public class PlaniData {
        public readonly ushort Gala, Sys, Pla;
        public readonly String Planityp, Objekttyp, Ownername, Ownerally, Planiname;
        internal PlaniData(MySqlDataReader r) {
			StringBuilder sb = new StringBuilder();
            Gala = r.GetUInt16(0);
            Sys = r.GetUInt16(1);
            Pla = r.GetUInt16(2);
            Planityp = r.GetString(3);
            Objekttyp = r.GetString(4);
            Ownername = r.GetString(5);
            Ownerally = r.IsDBNull(6) ? "" : r.GetString(6);
			Planiname = r.GetString(7);
        }
    }
}

namespace IWDB.Parser {
    public class IWDBParser : IWDBCore {
        Dictionary<String, RequestHandler> handlers;
        MySqlConnection mysql;
        Socket listeningSocket;
        String DBPrefix;
        IWDBParserModule parserMod;
		KabaFilter kabaFilter;
		WarFilter warFilter;
		TechTreeKostenCache techKostenCache;

        List<String> usersLoggedIn;
        Dictionary<String, List<string>> checkingUsers;
        public event NeueFlottenGesichtetDelegate OnNeueFlottenGesichtet;
		public event NeueKbGesichtet OnNeueKbGesichtet;

        internal IWDBParser(XmlNode config, IWDBParserModule parserMod) {
            if (config["name"] == null)
                throw new Exception("IWDBParser ohne Name!");
            if (config["ip"] == null || config["port"] == null || config["mysql"].InnerText == null || config["dbprefix"] == null)
                throw new Exception("Config von IWDBParser '"+config["name"].InnerText+"' fehlerhaft!");

            DBPrefix = config["dbprefix"].InnerText;

            mysql = parserMod.GetMysqlConnection(config["mysql"].InnerText);
			//IRCeX.Log.WriteLine("MySqlOpen: IWDBParser constructor");
			Monitor.Enter(mysql);
			try {
				mysql.Open();

				handlers = new Dictionary<string, RequestHandler>();
				techKostenCache = new TechTreeKostenCache();
				warFilter = new WarFilter(DBPrefix, mysql, techKostenCache);
				AddHandler(warFilter);
				AddHandler(new NewscanHandler(mysql, DBPrefix, config["mysql"].InnerText, this, warFilter, techKostenCache));
				AddHandler(new BauschleifenHandler());
				AddHandler(new TechTreeDepthHandler(mysql, DBPrefix));
				kabaFilter = new KabaFilter(DBPrefix, mysql);
				AddHandler(kabaFilter);
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: IWDBParser constructor");
				mysql.Close();
				Monitor.Exit(mysql);
			}
            usersLoggedIn = new List<string>();

            listeningSocket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
            IPEndPoint ep = new IPEndPoint(IPAddress.Parse(config["ip"].InnerText), int.Parse(config["port"].InnerText));
            listeningSocket.Bind(ep);
            listeningSocket.Listen(5);
            parserMod.RegisterListeningSocket(listeningSocket, NetworkCallback);
            parserMod.RegisterRawCommand(IrcCommand.NICK, UserNickChangeHandler);
            parserMod.RegisterRawCommand(IrcCommand.RPL_WHOISUSER, WhoisCallback);
            this.parserMod = parserMod;
            checkingUsers = new Dictionary<string, List<string>>();
        }
        void AddHandler(RequestHandler h) {
            handlers.Add(h.Name, h);
        }
        private void NetworkCallback(NetworkMessage Msg) {
            try {
                Socket s = ((NewConnectionNetworkMessage)Msg).NewSocket;
                s.ReceiveTimeout = 1000;
                StringBuilder sb = new StringBuilder();
                byte b = 0;
                byte previous;
                byte[] buffer = new byte[512];
                int read;
                bool finished = false;
                bool cancelled = false;
                ParserRequestMessage msg = new ParserRequestMessage(s);
                ParserRequestMessagePart part = msg.NextPart();
                while (!finished) {
                    read = s.Receive(buffer, 512, SocketFlags.None);
                    if (read == 0) {
                        cancelled = true;
                        break;
                    }
                    for (int i = 0; i < read; ++i) {
                        previous = b;
                        b = buffer[i];
                        if (b == 0) {
                            if (previous == 0) { // zwei Nullen => Ende der Nachricht
                                finished = true;
                                break;
                            }
                            else {
                                part = msg.NextPart();
                            }
                        }
                        else {
                            part.Add(b);
                        }
                    }
                }
                if (cancelled)
                    return;
                msg.Finished();

                RequestHandler handler;
                if (handlers.TryGetValue(msg[0].AsString, out handler)) {
                    handler.HandleRequest(msg);
                }
                else {
                    msg.AnswerLine("Protocol Mismatch.");
                }
            }
            catch (IOException e) {
                Log.WriteLine(LogLevel.E_NOTICE, "IWDBParser exception");
                Log.WriteException(e);
            }
            catch (SocketException e) {
                Log.WriteLine(LogLevel.E_NOTICE, "IWDBParser exception");
                Log.WriteException(e);
            }
        }

        private bool WhoisCallback(IRCMessage Msg) {
            if (Msg.Count < 3)
                return true;
            String nick = Msg[1];
            String username = Msg[2];
            String host = Msg[3];
            List<string> chans;
            if (checkingUsers.TryGetValue(nick, out chans)) {
                CheckLogin(nick, username, host);
                if (!parserMod.UserHasAccessLevel(nick, IRCModuleUserAccess.Normal)) {
                    foreach (string chan in chans) {
                        parserMod.SendRawMessage("KICK " + chan + " " + nick + " :Deine Hostmaske wurde nicht erkannt, bitte im Tool in den Einstellungen eintragen!");
                    }
                }
                checkingUsers.Remove(nick);
            }
            return true;
        }

        #region IWDBCore
        public void CheckLogin(string nick, string username, string host) {
            if (parserMod.IsLoggedIn(nick))
                return;
			//Log.WriteLine("MySqlOpen: CheckLogin");
			Monitor.Enter(mysql);
            mysql.Open();
			try {
				MySqlCommand cmd = new MySqlCommand("SELECT access FROM " + DBPrefix + "irc_autologin WHERE ?mask LIKE mask", mysql);
				cmd.Parameters.Add("?mask", MySqlDbType.String).Value = nick + "!" + username + "@" + host;
				object ret = cmd.ExecuteScalar();
				if(ret == null || Convert.IsDBNull(ret))
					return;
				IRCModuleUser u = new IRCModuleUser(nick, (IRCModuleUserAccess)(sbyte)ret);
				parserMod.LoginUser(nick, u);
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: CheckLogin");
				mysql.Close();
				Monitor.Exit(mysql);
			}
            int pos = ~usersLoggedIn.BinarySearch(nick);
            usersLoggedIn.Insert(pos, nick);
            parserMod.SendRawMessage("NOTICE " + nick + " :Du wurdest automatisch angemeldet!");
        }
        public void CheckLogout(string nick, string username, string host) {
            Log.WriteLine("Logging "+nick+" out (IWDBParser.CheckLogout)");
            int pos = usersLoggedIn.BinarySearch(nick);
            if (pos >= 0) {
                usersLoggedIn.RemoveAt(pos);
                if (parserMod.IsLoggedIn(nick)) {
                    parserMod.LogoutUser(nick);
                    parserMod.SendRawMessage("NOTICE " + nick + " :Du wurdest automatisch abgemeldet!");
                }
            }
        }
        public void CheckUsers(String chan, List<string> users) {
            foreach (String user in users) {
                List<String> chans;
                if (!checkingUsers.TryGetValue(user, out chans)) {
                    chans = new List<string>();
                    checkingUsers.Add(user, chans);
                    parserMod.SendRawMessage("WHOIS :" + user);
                }
                chans.Add(chan);
            }
        }
        public void BauleerlaufInfo(out int anz, out List<Pair<int, String>> neu) {
			//IRCeX.Log.WriteLine("MySqlOpen: BauleerlaufInfo");
			Monitor.Enter(mysql);
            mysql.Open();
			try {
				DateTime now = DateTime.Now;
				MySqlCommand neuerLeerlaufQry = new MySqlCommand("SELECT uid, igmname FROM (SELECT uid, MAX(end) AS end, igmname FROM " + DBPrefix + "building AS building INNER JOIN " + DBPrefix + "igm_data AS igm_data ON building.uid=igm_data.id WHERE igm_data.ikea = 0 OR plani=0 GROUP BY uid, plani) AS tmp GROUP BY tmp.uid HAVING min(tmp.end) BETWEEN ?time AND ?now", mysql);
				neuerLeerlaufQry.Parameters.Add("?time", MySqlDbType.UInt32).Value = IWDBUtils.toUnixTimestamp(now.AddSeconds(-IWDBChanModule.BauleerlaufSpamIntervalInSeconds));
				neuerLeerlaufQry.Parameters.Add("?now", MySqlDbType.UInt32).Value = IWDBUtils.toUnixTimestamp(now);
				MySqlDataReader r = neuerLeerlaufQry.ExecuteReader();
				neu = new List<Pair<int, string>>();
				while(r.Read()) {
					neu.Add(new Pair<int, String>(r.GetInt32(0), r.GetString(1)));
				}
				r.Close();

				MySqlCommand cmd = new MySqlCommand("SELECT COUNT(*) FROM (SELECT 1 FROM (SELECT uid, MAX(end) AS end FROM " + DBPrefix + "building AS building INNER JOIN " + DBPrefix + "igm_data AS igm_data ON building.uid=igm_data.id WHERE igm_data.ikea = 0 OR plani=0 GROUP BY uid, plani) AS tmp GROUP BY tmp.uid HAVING min(tmp.end) <= ?time) AS temp2", mysql);
				cmd.Parameters.Add("?time", MySqlDbType.UInt32).Value = IWDBUtils.toUnixTimestamp(now.AddSeconds(-IWDBChanModule.BauleerlaufSpamIntervalInSeconds));
				anz = Convert.ToInt32(cmd.ExecuteScalar());
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: BauleerlaufInfo");
				mysql.Close();
				Monitor.Exit(mysql);
			}
        }
        public void SitterauftraegeOffen(out int anz, out int jobid, out DateTime next) {
            next = DateTime.MinValue;
			//Log.WriteLine("MySqlOpen: SitterauftraegeOffen");
			Monitor.Enter(mysql);
			try {
				mysql.Open();

				uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);
				MySqlCommand aktJobCnt = new MySqlCommand("SELECT ID FROM " + DBPrefix + "sitter WHERE done=0 AND FollowUpTo=0 AND time<=?time ORDER BY time", mysql);
				aktJobCnt.Parameters.Add("?time", MySqlDbType.UInt32).Value = now;
				aktJobCnt.Prepare();
				MySqlDataReader r = aktJobCnt.ExecuteReader();
				//object ret = aktJobCnt.ExecuteScalar();
				anz = 0; jobid = 0;
				while(r.Read()) {
					if(anz == 0)
						jobid = r.GetInt32(0);
					anz++;
				}
				r.Close();

				MySqlCommand nextJob = new MySqlCommand("SELECT time FROM " + DBPrefix + "sitter WHERE done=0 AND time>?time ORDER BY time", mysql);
				nextJob.Parameters.Add("?time", MySqlDbType.UInt32).Value = now;
				nextJob.Prepare();
				object ret = nextJob.ExecuteScalar();
				if(ret != null && !Convert.IsDBNull(ret)) {
					uint time = Convert.ToUInt32(ret);
					next = IWDBUtils.fromUnixTimestamp(time);
				}
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: SitterauftraegeOffen");
				mysql.Close();
				Monitor.Exit(mysql);
			}
        }
        public void AnfliegendeFlotten(out uint flottenAnz, out uint zielplaniAnz) {
			//IRCeX.Log.WriteLine("MySqlOpen: AnfliegendeFlotten");
			Monitor.Enter(mysql);
			try {
				mysql.Open();

				uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);
				MySqlCommand aktFlottenUnterwegs = new MySqlCommand("SELECT COUNT(*), COUNT(DISTINCT zielid) FROM " + DBPrefix + @"flotten WHERE ankunft BETWEEN ?now AND ?soon
AND action IN('Angriff', 'Sondierung (Gebäude/Ress)', 'Sondierung (Schiffe/Def/Ress)')", mysql);
				aktFlottenUnterwegs.Parameters.Add("?now", MySqlDbType.UInt32).Value = now;
				aktFlottenUnterwegs.Parameters.Add("?soon", MySqlDbType.UInt32).Value = now + 600;
				MySqlDataReader r = aktFlottenUnterwegs.ExecuteReader();
				if(r.Read()) {
					flottenAnz = (uint)(r.GetUInt64(0));
					zielplaniAnz = (uint)(r.GetUInt64(1));
				} else {
					flottenAnz = zielplaniAnz = 0;
				}
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: AnfliegendeFlotten");
				mysql.Close();
				Monitor.Exit(mysql);
			}
        }
        public List<PlaniData> PlanisMitBesitzer(String name) {
			//IRCeX.Log.WriteLine("MySqlOpen: PlanisMitBesitzer");
			Monitor.Enter(mysql);
			try {
				mysql.Open();

				StringBuilder qry = new StringBuilder("SELECT gala, sys, pla, planityp, objekttyp, ownername, allytag, planiname FROM ");
				qry.Append(DBPrefix);
				qry.Append("universum AS universum LEFT JOIN ");
				qry.Append(DBPrefix);
				qry.Append("uni_userdata AS uni_userdata ON universum.ownername=uni_userdata.name WHERE ownername LIKE ?arg");
				MySqlCommand dataQry = new MySqlCommand(qry.ToString(), mysql);
				dataQry.Parameters.Add("?arg", MySqlDbType.String).Value = name;

				MySqlDataReader r = dataQry.ExecuteReader();
				List<PlaniData> ret = new List<PlaniData>();
				while(r.Read()) {
					ret.Add(new PlaniData(r));
				}
				r.Close();
				return ret;
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: PlanisMitBesitzer");
				mysql.Close();
				Monitor.Exit(mysql);
			}
        }
        public List<PlaniData> PlanisInSystem(uint gala, uint sys) {
			//IRCeX.Log.WriteLine("MySqlOpen: PlanisImSystem");
			Monitor.Enter(mysql);
			try {
				mysql.Open();

				StringBuilder qry = new StringBuilder("SELECT gala, sys, pla, planityp, objekttyp, ownername, allytag, planiname FROM ");
				qry.Append(DBPrefix);
				qry.Append("universum AS universum LEFT JOIN ");
				qry.Append(DBPrefix);
				qry.Append("uni_userdata AS uni_userdata ON universum.ownername=uni_userdata.name WHERE gala=?gal AND sys=?sys");
				MySqlCommand dataQry = new MySqlCommand(qry.ToString(), mysql);
				dataQry.Parameters.Add("?gal", MySqlDbType.UInt16).Value = gala;
				dataQry.Parameters.Add("?sys", MySqlDbType.UInt16).Value = sys;

				MySqlDataReader r = dataQry.ExecuteReader();
				List<PlaniData> ret = new List<PlaniData>();
				while(r.Read()) {
					ret.Add(new PlaniData(r));
				}
				r.Close();
				return ret;
			} finally {
				//IRCeX.Log.WriteLine("MySqlClose: PlanisImSystem");
				mysql.Close();
				Monitor.Exit(mysql);
			}
        }
        #endregion
        protected bool UserNickChangeHandler(IRCMessage Msg) {
            int pos = usersLoggedIn.BinarySearch(Msg.SenderNick);
            if (pos >= 0) {
                usersLoggedIn.RemoveAt(pos);
                String newNick = Msg[0];
                pos = ~usersLoggedIn.BinarySearch(newNick);
                usersLoggedIn.Insert(pos, newNick);
            }
            return true;
        }
        internal void NeueFlottenGesichtet(String ziel, int anz, bool angriff) {
            if (OnNeueFlottenGesichtet != null)
                OnNeueFlottenGesichtet(ziel, anz, angriff);
        }

		internal void NeueKbGesichtet(uint gala, uint sys, uint pla, String ownerName, String ownerAlly) {
			if(OnNeueKbGesichtet != null && kabaFilter.ApplyFilter(gala, sys, pla, ownerName, ownerAlly))
				OnNeueKbGesichtet(gala, sys, pla, ownerName, ownerAlly);
		}

        internal void Disable() {
            mysql = null;
            parserMod.UnregisterListeningSocket(listeningSocket);
            parserMod.UnregisterRawCommand(IrcCommand.NICK, UserNickChangeHandler);
            parserMod.UnregisterRawCommand(IrcCommand.RPL_WHOISUSER, WhoisCallback);
        }
		internal void IrcConnectionLost() {
			usersLoggedIn.Clear();
            checkingUsers.Clear();
		}
    }
	internal class IWDBParserModule:IIRCConModule, IWDBParserManager {

		IRCModularConnection con;
		XmlNode config;
        Dictionary<String, IWDBParser> parsers;
		
		public IWDBParserModule() {

		}

		#region IIRCConModule Member

		bool IIRCConModule.Disable() {
            foreach (IWDBParser p in parsers.Values) {
                p.Disable();
            }
            parsers.Clear();
			con.UnregisterUtil("iwdb", this, this);
			return true;
		}

		bool IIRCConModule.Enable() {
            parsers = new Dictionary<string, IWDBParser>();
            foreach (XmlNode n in config.SelectNodes("parser")) {
                IWDBParser parser = new IWDBParser(n, this);
                parsers.Add(n["name"].InnerText, parser);
            }
			con.RegisterUtil("iwdb", this, this);
			con.OnDisconnected += new EventHandler(con_OnDisconnected);
			return true;
		}

		void con_OnDisconnected(object sender, EventArgs e) {
			foreach (IWDBParser p in parsers.Values) {
				p.IrcConnectionLost();
			}
		}

		bool IIRCConModule.ForceDisable() {
			return ((IIRCConModule)this).Disable();
		}

		string IIRCConModule.Name {
			get { return "iwdbcore"; }
		}

		void IIRCConModule.Registered(IRCModularConnection Host, XmlNode ModuleConfig) {
			con = Host;
			config = ModuleConfig;
		}

		#endregion
        internal void RegisterRawCommand(IrcCommand cmd, IrcMessageHandler callback) {
            con.RegisterRawCommand(this, cmd, callback);
        }
        internal void RegisterListeningSocket(Socket listeningSocket, NetworkCallback callback) {
            con.RegisterListeningSocket(listeningSocket, callback);
        }
        internal MySqlConnection GetMysqlConnection(string connectionString) {
            return con.MysqlConnections.GetConnection(connectionString);
        }
        internal bool IsLoggedIn(String username) {
            return con.IsLoggedIn(username);
        }
        internal void LogoutUser(String username) {
            con.LogoutUser(username);
        }
        internal void LoginUser(String nick, IRCModuleUser username) {
            con.LoginUser(nick, username);
        }
        internal bool UserHasAccessLevel(String nick, IRCModuleUserAccess access) {
            return con.UserHasAccessLevel(nick, access);
        }
        internal void SendRawMessage(String msg) {
            con.SendRawMessage(msg);
        }

        internal void UnregisterListeningSocket(Socket listeningSocket) {
            con.UnregisterListeningSocket(listeningSocket);
        }
        internal void UnregisterRawCommand(IrcCommand cmd, IrcMessageHandler callback) {
            con.UnregisterRawCommand(this, cmd, callback);
        }
        public IWDBCore GetCore(String name) {
            return parsers[name];
        }
    }
	public class IWDBParserGen:IIRCConModuleFactory {
		#region IIRCConModuleFactory Member

		public string ModuleName {
			get { return "iwdbcore"; }
		}

		public IIRCConModule createInstance() {
			return new IWDBParserModule();
		}

		#endregion
	}
class ParserRequestMessagePart {
		byte[] buf;
		int pos;
		String stringRepresentation;
		public ParserRequestMessagePart() {
			buf = new byte[512];
			pos = 0;
			stringRepresentation = null;
		}
		public void Add(byte b) {
			if (pos == buf.Length) {
				byte[] oldbuf = buf;
				buf = new byte[buf.Length * 2];
				oldbuf.CopyTo(buf, 0);
			}
			buf[pos++] = b;
		}
		public String AsString {
			get {
				if (stringRepresentation == null) {
					stringRepresentation = Encoding.UTF8.GetString(buf, 0, pos);
				}
				return stringRepresentation;
			}
		}
		public Int32 ParseInt32() {
			return int.Parse(AsString);
		}
		public override string ToString() {
			return AsString;
		}
	}
	class ParserRequestMessage {
		List<ParserRequestMessagePart> parts;
		StreamWriter w;
        Socket s;
		public ParserRequestMessage(Socket s) {
			parts = new List<ParserRequestMessagePart>();
			this.w = new StreamWriter(new NetworkStream(s), Encoding.UTF8);
            this.s = s;
		}
		public ParserRequestMessagePart NextPart() {
			ParserRequestMessagePart ret = new ParserRequestMessagePart();
			parts.Add(ret);
			return ret;
		}
		public ParserRequestMessagePart this[int i] { get { return parts[i]; } }

		internal void Finished() {
			parts.RemoveAt(parts.Count - 1);
		}

		public void Answer(String str) {
			w.Write(str);
		}
		public void AnswerLine(String str) {
			w.Write(str);
			w.Write('\n');
		}
        public void Handled() {
            w.Close();
            s.Shutdown(SocketShutdown.Both);
            s.Close();
        }
	}

	interface RequestHandler {
		void HandleRequest(ParserRequestMessage msg);
		String Name { get;}
	}


	class BauschleifenHandler : IWDBRegex, RequestHandler {
		#region RequestHandler Member

		public void HandleRequest(ParserRequestMessage msg) {
			String req = msg[2].AsString;
			List<uint> entries = new List<uint>();

			Match planetenBauschleife = Regex.Match(req, @"aktuell im Bau auf diesem Planeten((?:\s+.*?bis\s+" + IWZeit + @"\n(?:1\sTag\s)?(?:\d+\sTage\s)?\d+:\d+:\d+)+)");
			if (planetenBauschleife.Success) {
				MatchCollection matches = Regex.Matches(planetenBauschleife.Groups[1].Value, @"\s+.*?bis\s+(" + IWZeit + @")\n(?:1\sTag\s)?(?:\d+\sTage\s)?\d+:\d+:\d+");
				foreach (Match m in matches) {
					entries.Add(IWDBUtils.parseIWTime(m.Groups[1].Value));
				}
			} else {
				Match ausbaustatus = Regex.Match(req, @"Ausbaustatus((?:\s+" + KolonieName + @"\s+" + Koordinaten + @".*?bis\s+" + IWZeit + @"(?:\s+|\s-\s)\d+:\d+:\d+)+)");
				if (ausbaustatus.Success) {
					MatchCollection c = Regex.Matches(ausbaustatus.Groups[1].Value, KolonieName + @"\s+\(" + msg[1].AsString + @"\).*?bis\s+(" + IWZeit + @")(?:\n|\s-\s)");
					foreach (Match m in c) {
						entries.Add(IWDBUtils.parseIWTime(m.Groups[1].Value));
					}
				}
			}
			if (entries.Count == 0) {
				msg.Answer("err");
			} else {
				foreach (uint entry in entries) {
					msg.AnswerLine(entry.ToString());
				}
			}
            msg.Handled();
		}

		public string Name {
			get { return "buildingqueue"; }
		}

		#endregion
	}
}
