using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using System.Globalization;
using System.Text;
using System.Threading;
using System.Linq;

namespace IWDB.Parser {

	class NewscanHandler : RequestHandler {
        Dictionary<String, Dictionary<String, List<ReportParser>>> parsers;
		Dictionary<String, IPostRequestHandler> postRequestHandler;
        IWDBParser parser;
		public readonly String DBPrefix;
		internal readonly Dictionary<String, Object> caches;
        SingleNewscanRequestHandler realRequestHandler;
		WarFilter warFilter;
		TechTreeCache techKostenCache;

		public NewscanHandler(MySqlConnection con, String DBPrefix, String DBConnection, IWDBParser parser, WarFilter warFilter, TechTreeCache techKostenCache) {
			this.parser = parser;
			this.DBPrefix = DBPrefix;
			caches = new Dictionary<string, object>();
            parsers = new Dictionary<string, Dictionary<string, List<ReportParser>>>();

            postRequestHandler = new Dictionary<string, IPostRequestHandler>();
			RegisterPostRequestHandler(new FlottenCleanupPostRequestHandler(this));
            realRequestHandler = new SingleNewscanRequestHandler(this, parsers, postRequestHandler, con, DBPrefix);
			this.warFilter = warFilter;
			this.techKostenCache = techKostenCache;
		}

        public List<ReportParser> CreateParsers(String Comma, String tsdSeperator) {
            Dictionary<string, List<ReportParser>> dict;
            if (!parsers.TryGetValue(Comma, out dict)) {
                 dict = new Dictionary<string, List<ReportParser>>();
                 parsers.Add(Comma, dict);
            }
            List<ReportParser> parserList = new List<ReportParser>();
            dict.Add(tsdSeperator, parserList);
            parserList.Add(new HauptseiteKolonieinformationParser(this, warFilter)); //Dieser Parser muss der erste der Hauptseitenparser sein!
            parserList.Add(new HauptseiteAusbaustatusParser(this));
            //parserList.Add(new UniversumsAnsichtParser(this));
            parserList.Add(new UniXMLUniversumsParser(this, this.parser));
            parserList.Add(new ScanLinkParser(this, warFilter));
            parserList.Add(new GebäudeinfoParser(this));
            parserList.Add(new ForschungsinfoParser(this));
            parserList.Add(new SchiffsinfoParser(this));
            parserList.Add(new TestParser(this));
            parserList.Add(new ForschungsübersichtParser(this));
            //parserList.Add(new UniversumsAnsichtStargateParser(this));
            parserList.Add(new GebäudeübersichtParser(this));
            parserList.Add(new HauptseiteFremdeFlottenParser(this, this.parser));
            parserList.Add(new HauptseiteFeindlicheFlottenParser(this, this.parser));
            parserList.Add(new RessourcenKoloÜbersichtParser(this));
            parserList.Add(new RessourcenKoloÜbersichtTeil2Parser(this));
            parserList.Add(new UniXMLLinkParser(this, this.parser));
            parserList.Add(new RessTransport(this));
			parserList.Add(new EigeneUebergabe(this));
			parserList.Add(new FremdeUebergabe(this));
            parserList.Add(new OperaDummyParser(this));
			parserList.Add(new KBParser(this, warFilter, techKostenCache));
			parserList.Add(new SchiffsKostenXmlParser(this));
            parserList.Add(new HighscoreParser(this));
            parserList.Add(new FremderScanParser(this));
            return parserList;
        }
        public bool HasParser(String comma, String dot, Type t) {
            Dictionary<String, List<ReportParser>> commaParsers;
            List<ReportParser> parserList;
            return parsers.TryGetValue(comma, out commaParsers) && commaParsers.TryGetValue(dot, out parserList) && parserList.Any(p => p.GetType() == t);
        }

		void RegisterPostRequestHandler(IPostRequestHandler newHandler) {
            postRequestHandler.Add(newHandler.Name, newHandler);
		}

		public CacheType RequestCache<CacheType>(String Name) where CacheType : new() {
			object ret;
			if (caches.TryGetValue(Name, out ret)) {
				return (CacheType)ret;
			} else {
				ret = new CacheType();
				caches.Add(Name, ret);
				return (CacheType)ret;
			}
		}

		#region RequestHandler Member

		public void HandleRequest(ParserRequestMessage msg) {
            realRequestHandler.Handle(msg);
		}

		public string Name {
			get { return "newscan"; }
		}

		#endregion

		
	}

    class SingleNewscanRequestHandler {
        Dictionary<String, Dictionary<String, List<ReportParser>>> parsers;
        Dictionary<String, IPostRequestHandler> activePostRequestHandler;
        Dictionary<String, IPostRequestHandler> postRequestHandler;
        NewscanHandler parentHandler;
        Thread t;
        MySqlConnection con;
        string dbPrefix;
        IRCeX.MessageQueue<ParserRequestMessage> Q = new IRCeX.MessageQueue<ParserRequestMessage>();

        BesonderheitenData besData;

        public SingleNewscanRequestHandler(NewscanHandler handler, Dictionary<String, Dictionary<String, List<ReportParser>>> parsers, Dictionary<string, IPostRequestHandler> postRequestHandlers, MySqlConnection con, String dbPrefix) {
            this.parsers=parsers;
            this.parentHandler = handler;
            this.postRequestHandler = postRequestHandlers;
            this.activePostRequestHandler = new Dictionary<string, IPostRequestHandler>();
            t = new Thread(threadMain);
            t.Start();
			this.con = con;
            this.dbPrefix = dbPrefix;
			//IRCeX.Log.WriteLine("MySqlOpen: SingleNewscanRequestHandler constructor");
			this.besData = new BesonderheitenData(con, dbPrefix);
        }

        public void ActivatePostHandler(String name) {
            if (!activePostRequestHandler.ContainsKey(name))
                activePostRequestHandler.Add(name, postRequestHandler[name]);
        }

        internal void Handle(ParserRequestMessage Msg) {
            Q.SendMessage(Msg);
        }

        private void threadMain() {
            while (true) {
                try {
                    ParserRequestMessage toHandle = Q.RecvMessage();
                    //IRCeX.Log.WriteLine("MySqlOpen: SingleNewscanRequestHandler");
                    Monitor.Enter(con);
                    try {
                        con.Open();
                        uint posterID = uint.Parse(toHandle[1].AsString);
                        uint victimID = uint.Parse(toHandle[2].AsString);
                        bool warmode = toHandle[4].AsString == "1";
                        bool restrictedUser = toHandle[5].AsString == "1";
                        String str = toHandle[6].AsString;

                        MySqlCommand envQry = new MySqlCommand("SELECT Komma, tsdTrennZeichen FROM " + DBPrefix + "igm_data where id=?id", con);
                        envQry.Parameters.Add("?id", MySqlDbType.UInt32).Value = victimID;
                        MySqlDataReader r = envQry.ExecuteReader();
                        if (!r.Read()) {
                            r.Close();
                            toHandle.Answer("Fehler: Unbekannte UID!");
                            toHandle.Handled();
                            continue;
                        }
                        String Komma = r.GetString(0);
                        String tsdTrennZeichen = r.GetString(1);
                        r.Close();
                        CultureInfo culture = (CultureInfo)CultureInfo.CurrentCulture.Clone();
                        NumberFormatInfo numberFormat = (NumberFormatInfo)NumberFormatInfo.CurrentInfo.Clone();
                        numberFormat.NumberDecimalSeparator = Komma;
                        numberFormat.NumberGroupSeparator = tsdTrennZeichen;
                        culture.NumberFormat = numberFormat;
                        System.Threading.Thread.CurrentThread.CurrentCulture = culture;
                        ParserResponse resp = new ParserResponse();

                        Dictionary<String, List<ReportParser>> parsersWithComma;
                        List<ReportParser> parserList;
                        if (!parsers.TryGetValue(Komma, out parsersWithComma) || !parsersWithComma.TryGetValue(tsdTrennZeichen, out parserList)) {
                            parserList = parentHandler.CreateParsers(Komma, tsdTrennZeichen);
                        }
                        PatternFlags browserFlags = GetBrowserFlags(toHandle[3].AsString);

                        //dieser workaround ist notwendig, da der Universumsansicht-Parser 2 aufeinander folgende Uniansichten nicht auseinander halten kann
                        String[] parts = str.Split(new string[] { "\nHILFE\nPostit erstellen", "HILFE\n\nWerde IceWars Supporter" }, StringSplitOptions.RemoveEmptyEntries);
                        MySqlCommand perfLog = new MySqlCommand("INSERT INTO " + DBPrefix + "speedlog (action, sub, runtime) VALUES ('parser', ?p, ?rt)", con);
                        MySqlParameter pParser = perfLog.Parameters.Add("?p", MySqlDbType.String);
                        MySqlParameter pRT = perfLog.Parameters.Add("?rt", MySqlDbType.UInt32);
                        foreach (String part in parts) {
                            foreach (ReportParser parser in parserList) {
                                DateTime start = DateTime.Now;
                                parser.TryMatch(part, posterID, victimID, browserFlags, warmode, restrictedUser, con, this, resp);
                                TimeSpan parseTime = DateTime.Now - start;
                                pParser.Value = parser.ToString();
                                pRT.Value = parseTime.Ticks;
                                perfLog.ExecuteNonQuery();
                            }
                        }
                        toHandle.Answer(resp.ToString());

                        foreach (IPostRequestHandler postReqH in activePostRequestHandler.Values) {
                            postReqH.HandlePostRequest(con, dbPrefix);
                        }
                        activePostRequestHandler.Clear();
                        toHandle.Handled();
                    } catch (Exception e) {
                        if (e is SynchronizationLockException)
                            throw;
                        if (e is ThreadAbortException) {
                            try {
                                toHandle.Answer("timeout killed");
                                toHandle.Handled();
                            } catch (Exception) { }
                            throw;
                        }
                        ParserResponse errResp = new ParserResponse();
                        errResp.RespondError("<b>Schwerer Parserfehler:</b><br />" + e.ToString());
                        try {
                            toHandle.Answer(errResp.ToString());
                            toHandle.Handled();
                        } catch (Exception ex) {
                            IRCeX.Log.WriteLine(IRCeX.LogLevel.E_ERROR, "Fehler im Exceptionhandler :O");
                            IRCeX.Log.WriteException(e);
                            IRCeX.Log.WriteException(ex);
                        }
                    }
                } finally {
                    //IRCeX.Log.WriteLine("MySqlClose: SingleNewscanRequestHandler");
                    con.Close();
                    Monitor.Exit(con);
                }
            }
        }

        
        private PatternFlags GetBrowserFlags(String userAgent) {
            if (userAgent.Contains("Firefox"))
                return PatternFlags.Firefox;
            if (userAgent.Contains("Opera"))
                return PatternFlags.Opera;
            return PatternFlags.Other;
        }

        public BesonderheitenData BesData { get { return besData; } }
        public String DBPrefix { get { return this.dbPrefix; } }
        public MySqlConnection Con { get { return con; } }
    }

    public enum PatternFlags {
        All = ~0,
        Firefox = 1,
        Opera = 2,
        Other = 4,
    }

    abstract class ReportParser:IWDBRegex {
        
		protected readonly String DBPrefix;
        private readonly List<Tuple<Regex, String, PatternFlags>> regexes;
        private readonly NewscanHandler parent;
		private bool allowRestricted = true;

		public abstract void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp);
        public ReportParser(NewscanHandler newscanHandler) {
            regexes = new List<Tuple<Regex, String, PatternFlags>>();
            this.DBPrefix = newscanHandler.DBPrefix;
            this.parent = newscanHandler;
        }
		public ReportParser(NewscanHandler newscanHandler, bool allowRestricted):this(newscanHandler) {
			this.allowRestricted = allowRestricted;
		}
        protected void AddPattern(String pattern) {
            AddPattern(pattern, PatternFlags.All);
        }
        protected void AddPattern(String pattern, PatternFlags flags) {
            AddPattern(pattern, null, flags);
        }
        protected void AddPattern(String pattern, String prefilter, PatternFlags flags) {
            regexes.Add(new Tuple<Regex, String, PatternFlags>(new Regex(pattern, RegexOptions.Compiled | RegexOptions.IgnorePatternWhitespace), prefilter, flags));
        }
        protected void Requires(Type t) {
            if (!parent.HasParser(Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator, Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator, t))
                throw new InvalidOperationException(this.GetType()+ " benötigt einen " + t.ToString());
        }
        public void TryMatch(String haystack, uint posterID, uint victimID, PatternFlags browserFlags, bool warmode, bool restrictedUser, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			if(!allowRestricted && restrictedUser)
				return;
            foreach (Tuple<Regex, String, PatternFlags> p in regexes) {
                if ((browserFlags & p.Item3) == 0)
                    continue;
                if (p.Item2 != null && !haystack.Contains(p.Item2))
                    continue;
                MatchCollection matches = p.Item1.Matches(haystack);
                if (matches.Count > 0) {
                    Console.WriteLine(this.GetType().Name);
                    Matched(matches, posterID, victimID, con, handler, resp);
                    break;
                }
            }
        }
		protected CacheType RequestCache<CacheType>(String Name) where CacheType:new() {
			object ret;
			if (parent.caches.TryGetValue(Name, out ret)) {
				return (CacheType)ret;
			} else {
				ret = new CacheType();
				parent.caches.Add(Name, ret);
				return (CacheType)ret;
			}
		}
	}
	interface IPostRequestHandler {
		void HandlePostRequest(MySqlConnection con, String DBPrefix);
		String Name { get; }
	}

	class TestParser : ReportParser {
        public TestParser(NewscanHandler newscanHandler) : base(newscanHandler, false) { AddPattern("thisisatest"); }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			resp.Respond("retest");
			resp.Respond("retest");
			resp.Respond("retestv2");
			resp.RespondError("fehlertest");
			resp.RespondError("fehlertest");
			resp.RespondError("fehlertestv2");
		}
	}
    class OperaDummyParser : ReportParser {
        public OperaDummyParser(NewscanHandler h):base(h) {
            AddPattern(@"Gebäudeinfo:[\s\S]+?Farbenlegende:", PatternFlags.Opera);
            AddPattern(@"Forschungsinfo:\s+(.+)[\s\S]+?Farbenlegende:", PatternFlags.Opera);
            AddPattern(@"Schiffinfo:\s+", PatternFlags.Opera);
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            resp.RespondError("Bitte für Gebäudeinfo, Schiffsinfo, Forschungsinfo den Firefox benutzen!");
        }
    }


	class ParserResponse {
		Dictionary<String, int> responses;
		public ParserResponse() {
			responses = new Dictionary<string, int>();
		}
		protected void Respond(bool error, String message) {
            message = IRCeX.ConfigUtils.XmlEscape(message);
			if (responses.ContainsKey(message)) {
				if (error)
					responses[message]--;
				else
					responses[message]++;
			} else {
				responses.Add(message, error ? -1 : 1);
			}
		}
		public void Respond(String Message) {
			Respond(false, Message);
		}
		public void RespondError(String Message) {
			Respond(true, Message);
		}
		public override String ToString() {
			StringBuilder sb;
			StringBuilder negResp = new StringBuilder();
			StringBuilder posResp = new StringBuilder();
			foreach (KeyValuePair<String, int> resp in responses) {
				bool error = resp.Value < 0;
				int anz = Math.Abs(resp.Value);
				if (error) {
					sb = negResp;
					sb.Append("<div class='imp'>");
				} else {
					sb = posResp;
					sb.Append("<div class='simp'>");
				}
				if (anz > 1) {
					sb.Append(anz);
					sb.Append("x ");
				}
				sb.Append(resp.Key);
				sb.Append("</div>");
			}
			negResp.Append(posResp.ToString());
			return negResp.ToString();
		}
	}
}