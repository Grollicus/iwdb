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
        public bool ToDB(MySqlConnection con, String DBPrefix, BesonderheitenData dta, ParserResponse resp) {
            PlaniFetcher f = new PlaniFetcher(dta, con, DBPrefix) {Gala = zielGala, Sys=zielSys, Pla=zielPla};
            List<PlaniInfo> ids = f.FetchMatching(PlaniDataFlags.ID);
            if (ids.Count == 0) {
                resp.RespondError("Transportbericht übersprungen, Unidaten fehlerhaft bei " + zielGala + ":" + zielSys);
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
                resp.Respond("Bereits bekannten Transportbericht übersprungen!");
            else
                resp.Respond("Transportbericht eingelesen!");
            return true;
        }
    }
    class RessTransport : ReportParser {
        public RessTransport(NewscanHandler h)
            : base(h) {
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
                t.ToDB(con, DBPrefix, handler.BesData, resp);
            }
        }
    }
}
