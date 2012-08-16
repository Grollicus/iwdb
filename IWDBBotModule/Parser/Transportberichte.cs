using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;

namespace IWDB.Parser {
    class Transport {
        uint zielGala, zielSys, zielPla;
        String empfänger;
        String absender;
        uint ankunftszeit;
        int eisen = 0, stahl = 0, chem = 0, vv4a = 0, eis = 0, wasser = 0, energie = 0, bev = 0;

        public Transport(uint zielGala, uint zielSys, uint zielPla, String empfänger, String absender, uint ankunftszeit) {
            this.zielGala = zielGala;
            this.zielSys = zielSys;
            this.zielPla = zielPla;
            this.empfänger = empfänger;
            this.absender = absender;
            this.ankunftszeit = ankunftszeit;
        }
        public void SetRess(String name, int value) {
            switch (name) {
                case "Eisen":
                    eisen = value;
                    break;
                case "Stahl":
                    stahl = value;
                    break;
                case "VV4A":
                    vv4a = value;
                    break;
                case "chem. Elemente":
                    chem = value;
                    break;
                case "Eis":
                    eis = value;
                    break;
                case "Wasser":
                    wasser = value;
                    break;
                case "Energie":
                    energie = value;
                    break;
                case "Pinguine":
                    break;
                case "Bevölkerung":
                    bev = value;
                    break;
            }
        }
        public bool ToDB(MySqlConnection con, String DBPrefix, BesonderheitenData dta, ParserResponse resp, String desc) {
            PlaniFetcher f = new PlaniFetcher(dta, con, DBPrefix) {Gala = zielGala, Sys=zielSys, Pla=zielPla};
            List<PlaniInfo> ids = f.FetchMatching(PlaniDataFlags.ID);
            if (ids.Count == 0) {
                resp.RespondError(desc+" übersprungen, Unidaten fehlerhaft bei " + zielGala + ":" + zielSys);
                return false;
            }
            uint planid = ids[0].ID;
            MySqlCommand cmd = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + "bilanz (planid, empfaenger, absender, zeit, eisen, stahl, chemie, vv4a, eis, wasser, energie, bev) VALUES (?planid, ?empf, ?abs, ?zeit, ?fe, ?st, ?ch, ?vv, ?ei, ?wa, ?en, ?bev)", con);
            cmd.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
            cmd.Parameters.Add("?empf", MySqlDbType.String).Value = empfänger;
            cmd.Parameters.Add("?abs", MySqlDbType.String).Value = absender;
            cmd.Parameters.Add("?zeit", MySqlDbType.UInt32).Value = ankunftszeit;
            cmd.Parameters.Add("?fe", MySqlDbType.UInt32).Value = eisen;
            cmd.Parameters.Add("?st", MySqlDbType.UInt32).Value = stahl;
            cmd.Parameters.Add("?ch", MySqlDbType.UInt32).Value = chem;
            cmd.Parameters.Add("?vv", MySqlDbType.UInt32).Value = vv4a;
            cmd.Parameters.Add("?ei", MySqlDbType.UInt32).Value = eis;
            cmd.Parameters.Add("?wa", MySqlDbType.UInt32).Value = wasser;
            cmd.Parameters.Add("?en", MySqlDbType.UInt32).Value = energie;
            cmd.Parameters.Add("?bev", MySqlDbType.UInt32).Value = bev;
            if (cmd.ExecuteNonQuery() == 0)
                resp.Respond("Bereits bekannten "+desc+" übersprungen!");
            else
                resp.Respond(desc+" eingelesen!");
            return true;
        }
    }
    class RessTransport : ReportParser {
        public RessTransport(NewscanHandler h)
            : base(h, false) {
            String dot = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator;
            AddPatern(@"Transport\sangekommen\s" + KoordinatenEinzelMatch + @"\s+Systemnachricht\s+(" + PräziseIWZeit + @")\s+
Transport\s+
Eine\sFlotte\sist\sauf\sdem\sPlaneten\s" + KolonieName + @"\s+\d+:\d+:\d+\s+angekommen\.\s+Der\sAbsender\sist\s(" + SpielerName + @")\.\sDer\sEmpfänger\sist\s(" + SpielerName + @")\.\s+
.*\s+
Ressourcen
((?:\s" + RessourcenName + @"\s+" + Number + @")+)\s");
        }
        public override void Matched(System.Text.RegularExpressions.MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            foreach (Match outerMatch in matches) {
                Transport t = new Transport(
                    uint.Parse(outerMatch.Groups[1].Value),
                    uint.Parse(outerMatch.Groups[2].Value),
                    uint.Parse(outerMatch.Groups[3].Value),
                    outerMatch.Groups[6].Value,
                    outerMatch.Groups[5].Value,
                    IWDBUtils.parsePreciseIWTime(outerMatch.Groups[4].Value)
                );
                MatchCollection c = Regex.Matches(outerMatch.Groups[7].Value, @"\s(" + RessourcenName + @")\s+(" + Number + ")");
                foreach (Match m in c) {
                    t.SetRess(m.Groups[1].Value, int.Parse(m.Groups[2].Value, System.Globalization.NumberStyles.Any));
                }
                t.ToDB(con, DBPrefix, handler.BesData, resp, "Transportbericht");
            }
        }
    }
	class EigeneUebergabe : ReportParser {
		public EigeneUebergabe(NewscanHandler h)
			: base(h, false) {
			AddPatern(@"Schiffe\sübergeben\s" + KoordinatenEinzelMatch + @"\s+Systemnachricht\s+(" + PräziseIWZeit + @")\s+
Übergabe\s+
Es\swurde\seine\sFlotte\sauf\sdem\sPlaneten\s" + KolonieName + @"\s\d+:\d+:\d+\sübergeben\.\sDer\sEmpfänger\sist\s(" + SpielerName + @")\s+
Es\swurden\sfolgende\sSachen\sübergeben\s+
Schiffe\s+
(.+?)\s+
Ressourcen
((?:\s+" + RessourcenName + @"\s+" + Number + @")+)\s");
		}

		public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			Dictionary<uint, String> uidToIgmNameCache = new Dictionary<uint, string>();
			foreach(Match outerMatch in matches) {
				String absender;
				if(!uidToIgmNameCache.TryGetValue(victimID, out absender)) {
					MySqlCommand igmNameQry = new MySqlCommand("SELECT igmname FROM " + DBPrefix + "igm_data WHERE id=?igmid", con);
					igmNameQry.Parameters.Add("?igmid", MySqlDbType.UInt32).Value = victimID;
					Object obj = igmNameQry.ExecuteScalar();
					absender = (String)obj;
					uidToIgmNameCache.Add(victimID, absender);
				}
				Transport t = new Transport(
					uint.Parse(outerMatch.Groups[1].Value),
					uint.Parse(outerMatch.Groups[2].Value),
					uint.Parse(outerMatch.Groups[3].Value),
					outerMatch.Groups[5].Value,
					absender,
					IWDBUtils.parsePreciseIWTime(outerMatch.Groups[4].Value)
				);
				MatchCollection c = Regex.Matches(outerMatch.Groups[7].Value, @"\s(" + RessourcenName + @")\s+(" + Number + ")");
				foreach(Match m in c) {
					t.SetRess(m.Groups[1].Value, int.Parse(m.Groups[2].Value, System.Globalization.NumberStyles.Any));
				}
				t.ToDB(con, DBPrefix, handler.BesData, resp, "Übergabebericht");
			}
		}
	}

	class FremdeUebergabe : ReportParser {
		public FremdeUebergabe(NewscanHandler h)
			: base(h, false) {
			AddPatern(@"Schiffe\sübergeben\s" + KoordinatenEinzelMatch + @"\s+Systemnachricht\s+(" + PräziseIWZeit + @")\s+
Übergabe\s+
Eine\sFlotte\sist\sauf\sdem\sPlaneten\s" + KolonieName + @"\s\d+:\d+:\d+\sangekommen\.\sDer\sAbsender\sist\s(" + SpielerName + @")\s+
Es\swurden\sfolgende\sSachen\sübergeben\s+
Schiffe\s+
(.+?)\s+
Ressourcen
((?:\s+" + RessourcenName + @"\s+" + Number + @")+)\s");
		}

		public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			Dictionary<uint, String> uidToIgmNameCache = new Dictionary<uint, string>();
			foreach(Match outerMatch in matches) {
				String empfaenger;
				if(!uidToIgmNameCache.TryGetValue(victimID, out empfaenger)) {
					MySqlCommand igmNameQry = new MySqlCommand("SELECT igmname FROM " + DBPrefix + "igm_data WHERE id=?igmid", con);
					igmNameQry.Parameters.Add("?igmid", MySqlDbType.UInt32).Value = victimID;
					Object obj = igmNameQry.ExecuteScalar();
					empfaenger = (String)obj;
					uidToIgmNameCache.Add(victimID, empfaenger);
				}
				Transport t = new Transport(
					uint.Parse(outerMatch.Groups[1].Value),
					uint.Parse(outerMatch.Groups[2].Value),
					uint.Parse(outerMatch.Groups[3].Value),
					empfaenger,
					outerMatch.Groups[5].Value,
					IWDBUtils.parsePreciseIWTime(outerMatch.Groups[4].Value)
				);
				MatchCollection c = Regex.Matches(outerMatch.Groups[7].Value, @"\s(" + RessourcenName + @")\s+(" + Number + ")");
				foreach(Match m in c) {
					t.SetRess(m.Groups[1].Value, int.Parse(m.Groups[2].Value, System.Globalization.NumberStyles.Any));
				}
				t.ToDB(con, DBPrefix, handler.BesData, resp, "Übergabebericht");
			}
		}
	}
}
