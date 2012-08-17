using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;

namespace IWDB.Parser {
    class HighscoreParser:ReportParser {
        public HighscoreParser(NewscanHandler h):base(h, false) {
            AddPattern(@"Highscore\s+Highscore\s+Letzte\sAktualisierung\s(" + IWZeit + @")\s+Manueller\sStart:[\s\S]*?Ordnung\snach\sPunkten\s+Pos\s+Name\s+Allianz\s+Gebpkt.\s+Forschpkt.\s+Gesamt\s+P.\s/\sTag\s+dabei\sseit
((?:\s+\d+\s+" + SpielerName + @"\s+(?:" + AllyTag + @")?\s+" + Number + "\\s+" + Number + "\\s+" + Number + "\\s+" + DecimalNumber + @"\s+-?" + Number + "\\s+" + Datum + ")+)");
        }
        public override void Matched(System.Text.RegularExpressions.MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            foreach (Match outerMatch in matches) {
                uint time = IWDBUtils.parseIWTime(outerMatch.Groups[1].Value);
                MatchCollection c = Regex.Matches(outerMatch.Groups[2].Value, @"(\d+)\s+("+SpielerName+@")\s+("+AllyTag+@")?\s+("+Number+")\\s+("+Number+")\\s+("+Number+")\\s+("+DecimalNumber+@")\s+(-?"+Number+")\\s+("+Datum+")");
                MySqlCommand ins = new MySqlCommand("INSERT IGNORE INTO "+DBPrefix+"highscore (time, pos, name, ally, gebp, forp, gesp, ppd, diff, dabei) VALUES (?time, ?pos, ?name, ?ally, ?gebp, ?forp, ?gesp, ?ppd, ?diff, ?dabei)", con);
                ins.Parameters.Add("?time", MySqlDbType.UInt32).Value = time;
                MySqlParameter pPos = ins.Parameters.Add("?pos", MySqlDbType.UInt32);
                MySqlParameter pName = ins.Parameters.Add("?name", MySqlDbType.String);
                MySqlParameter pAlly = ins.Parameters.Add("?ally", MySqlDbType.String);
                MySqlParameter pGebp = ins.Parameters.Add("?gebp", MySqlDbType.UInt32);
                MySqlParameter pForp = ins.Parameters.Add("?forp", MySqlDbType.UInt32);
                MySqlParameter pGesp = ins.Parameters.Add("?gesp", MySqlDbType.UInt32);
                MySqlParameter pPpd = ins.Parameters.Add("?ppd", MySqlDbType.Double);
                MySqlParameter pDiff = ins.Parameters.Add("?diff", MySqlDbType.Int32);
                MySqlParameter pDabei = ins.Parameters.Add("?dabei", MySqlDbType.UInt32);
                ins.Prepare();

                MySqlCommand insInactive = new MySqlCommand("INSERT INTO " + DBPrefix + @"highscore_inactive (name, since, until, gebp) VALUES (?name, ?since, ?until, ?gebp) 
                    ON DUPLICATE KEY UPDATE 
                        since = IF(gebp=VALUES(gebp),IF(since<VALUES(since),since,VALUES(since)), IF(since>VALUES(since),since,VALUES(since))), 
                        until = IF(VALUES(until)>until,VALUES(until),until),
                        gebp  = IF(until < VALUES(until), VALUES(gebp), gebp)", con);
                MySqlParameter pInsName = insInactive.Parameters.Add("?name", MySqlDbType.String);
                insInactive.Parameters.Add("?since", MySqlDbType.UInt32).Value = time;
                insInactive.Parameters.Add("?until", MySqlDbType.UInt32).Value = time;
                MySqlParameter pInsGebp = insInactive.Parameters.Add("?gebp", MySqlDbType.UInt32);
                insInactive.Prepare();

                foreach (Match m in c) {
                    pPos.Value = uint.Parse(m.Groups[1].Value);
                    pName.Value = m.Groups[2].Value;
                    pAlly.Value = m.Groups[3].Value;
                    pGebp.Value = uint.Parse(m.Groups[4].Value, System.Globalization.NumberStyles.Any);
                    pForp.Value = uint.Parse(m.Groups[5].Value, System.Globalization.NumberStyles.Any);
                    pGesp.Value = uint.Parse(m.Groups[6].Value, System.Globalization.NumberStyles.Any);
                    pPpd.Value = double.Parse(m.Groups[7].Value, System.Globalization.NumberStyles.Any);
                    pDiff.Value = int.Parse(m.Groups[8].Value, System.Globalization.NumberStyles.Any);
                    pDabei.Value = IWDBUtils.toUnixTimestamp(DateTime.ParseExact(m.Groups[9].Value, "dd.MM.yyyy", null, System.Globalization.DateTimeStyles.AssumeLocal|System.Globalization.DateTimeStyles.AdjustToUniversal));
                    if (ins.ExecuteNonQuery() == 0) {
                        resp.Respond("HS übersprungen!");
                        continue;
                    }

                    pInsName.Value = m.Groups[2].Value;
                    pInsGebp.Value = uint.Parse(m.Groups[4].Value, System.Globalization.NumberStyles.Any);
                    insInactive.ExecuteNonQuery();

                    resp.Respond("HS eingelesen!");
                }
            }
        }
    }
}
