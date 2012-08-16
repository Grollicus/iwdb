using System;
using System.Collections.Generic;
using System.Text;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;

namespace IWDB.Parser {
	class RessourcenKoloÜbersichtParser : ReportParser {
		public RessourcenKoloÜbersichtParser(NewscanHandler handler)
			: base(handler, false) {
            String dot = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator;
            String comma = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator;
            AddPattern(@"Ressourcenkoloübersicht\s+
Kolonie\s+Eisen\s+Stahl\s+VV4A\s+chem.\sElemente\s+Eis\s+Wasser\s+Energie\s+
[\s\S]+?Gesamt[\s\S]+?(?:\s+[\d" + dot + comma + @" ]+\s+\([-\d" + dot + comma + @"]+\)+)\s+Lager\sund\sBunker\sanzeigen", PatternFlags.All);
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);
			String dot = Regex.Escape(System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator);
			String comma = Regex.Escape(System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator);
			foreach (Match outerMatch in matches) {
				MatchCollection innerMatches = Regex.Matches(outerMatch.Value, "\n" + KolonieName + @"\s+(\d+):(\d+):(\d+)\s+
\((?:Kolonie|Kampfbasis|Sammelbasis)\)\s+((?:[\d" + dot + @"]+\s+
\([-\d" + dot + comma + @"]+\)\s+
(?:---|[\d" + dot + @"]+)\s+
[\d" + dot + @"]+\s){7})+", RegexOptions.IgnorePatternWhitespace);
				uint uid = 0;
                if (innerMatches.Count == 0)
                    return;
				foreach (Match innerMatch in innerMatches) {
					ResourceSet aktRess = new ResourceSet();
					ResourceSet vRess = new ResourceSet();
					ResourceSet lager = new ResourceSet();
					MatchCollection ressMatches = Regex.Matches(innerMatch.Groups[4].Value, @"([\d" + dot + @"]+)\s+
\(([-\d" + dot + comma + @"]+)\)\s+
(---|[\d" + dot + @"]+)\s+
[\d" + dot + @"]+\s", RegexOptions.IgnorePatternWhitespace);
					int i = 0;
					foreach (Match m in ressMatches) {
						aktRess.Set(i, m.Groups[1].Value);
						vRess.Set(i, m.Groups[2].Value);
						String str = m.Groups[3].Value;
						if (str != "---")
							lager.Set(i, str);
						++i;
					}
					uint gala = uint.Parse(innerMatch.Groups[1].Value);
					uint sys = uint.Parse(innerMatch.Groups[2].Value);
					uint pla = uint.Parse(innerMatch.Groups[3].Value);
					uint planid;
					if (uid == 0) {
						MySqlCommand uidPlanidQry = new MySqlCommand(@"SELECT universum.id , igm_data.id
							FROM " + DBPrefix + "universum AS universum LEFT JOIN " + DBPrefix + @"igm_data AS igm_data ON universum.ownername=igm_data.igmname
							WHERE universum.sys=?sys AND universum.gala=?gala AND universum.pla=?pla", con);
						uidPlanidQry.Parameters.Add("?sys", MySqlDbType.UInt32).Value = sys;
						uidPlanidQry.Parameters.Add("?gala", MySqlDbType.UInt32).Value = gala;
						uidPlanidQry.Parameters.Add("?pla", MySqlDbType.UInt32).Value = pla;
						MySqlDataReader r = uidPlanidQry.ExecuteReader();
						if (!r.Read() || r.IsDBNull(1)) {
							resp.RespondError("Universumsdaten fehlerhaft oder unvollständig!\n");
							r.Close();
							continue;
						}
						planid = r.GetUInt32(0);
						uid = r.GetUInt32(1);
						r.Close();
						MySqlCommand cleanupQry = new MySqlCommand(@"DELETE FROM " + DBPrefix + "ressuebersicht WHERE uid=?uid", con);
						cleanupQry.Parameters.Add("?uid", MySqlDbType.UInt32).Value = uid;
						cleanupQry.ExecuteNonQuery();
					} else {
						MySqlCommand planIdQry = new MySqlCommand("SELECT id from " + DBPrefix + "universum WHERE sys=?sys AND gala=?gala AND pla=?pla", con);
						planIdQry.Parameters.Add("?sys", MySqlDbType.UInt32).Value = sys;
						planIdQry.Parameters.Add("?gala", MySqlDbType.UInt32).Value = gala;
						planIdQry.Parameters.Add("?pla", MySqlDbType.UInt32).Value = pla;
						object res = planIdQry.ExecuteScalar();
						if (res == null || Convert.IsDBNull(res)) {
							resp.RespondError("Universumsdaten fehlerhaft oder unvollständig!\n");
							continue;
						}
						planid = (uint)res;
					}
					MySqlCommand insertQry = new MySqlCommand("INSERT INTO " + DBPrefix + @"ressuebersicht (uid, planid, fe, st, vv, ch, ei, wa, en, vFe, vSt, vVv, vCh, vEi, vWa, vEn, lCh, lEi, lWa, lEn, time) VALUES (
																									?uid, ?planid, ?fe, ?st, ?vv, ?ch, ?ei, ?wa, ?en, ?vFe, ?vSt, ?vVv, ?vCh, ?vEi, ?vWa, ?vEn, ?lCh, ?lEi, ?lWa, ?lEn, ?time) 
																	on duplicate key update uid=VALUES(uid), fe=VALUES(fe), st=VALUES(st), vv=VALUES(vv), ch=VALUES(ch), ei=VALUES(ei), wa=VALUES(wa), en=VALUES(en), vFe=VALUES(vFe), vSt=VALUES(vSt), vVv=VALUES(vVv), vCh=VALUES(vCh), vEi=VALUES(vEi), vWa=VALUES(vWa), vEn=VALUES(vEn), lCh=VALUES(lCh), lEi=VALUES(lEi), lWa=VALUES(lWa), lEn=VALUES(lEn), time=VALUES(time)", con);
					insertQry.Parameters.Add("?uid", MySqlDbType.UInt32).Value = uid;
					insertQry.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
					insertQry.Parameters.Add("?fe", MySqlDbType.UInt32).Value = aktRess.Eisen;
					insertQry.Parameters.Add("?st", MySqlDbType.UInt32).Value = aktRess.Stahl;
					insertQry.Parameters.Add("?vv", MySqlDbType.UInt32).Value = aktRess.VV4A;
					insertQry.Parameters.Add("?ch", MySqlDbType.UInt32).Value = aktRess.Chemie;
					insertQry.Parameters.Add("?ei", MySqlDbType.UInt32).Value = aktRess.Eis;
					insertQry.Parameters.Add("?wa", MySqlDbType.UInt32).Value = aktRess.Wasser;
					insertQry.Parameters.Add("?en", MySqlDbType.UInt32).Value = aktRess.Energie;
					insertQry.Parameters.Add("?vFe", MySqlDbType.Int32).Value = vRess.Eisen * 100;
					insertQry.Parameters.Add("?vSt", MySqlDbType.Int32).Value = vRess.Stahl * 100;
					insertQry.Parameters.Add("?vVv", MySqlDbType.Int32).Value = vRess.VV4A * 100;
					insertQry.Parameters.Add("?vCh", MySqlDbType.Int32).Value = vRess.Chemie * 100;
					insertQry.Parameters.Add("?vEi", MySqlDbType.Int32).Value = vRess.Eis * 100;
					insertQry.Parameters.Add("?vWa", MySqlDbType.Int32).Value = vRess.Wasser * 100;
					insertQry.Parameters.Add("?vEn", MySqlDbType.Int32).Value = vRess.Energie * 100;
					insertQry.Parameters.Add("?lCh", MySqlDbType.UInt32).Value = lager.Chemie;
					insertQry.Parameters.Add("?lEi", MySqlDbType.UInt32).Value = lager.Eis;
					insertQry.Parameters.Add("?lWa", MySqlDbType.UInt32).Value = lager.Wasser;
					insertQry.Parameters.Add("?lEn", MySqlDbType.UInt32).Value = lager.Energie;
					insertQry.Parameters.Add("?time", MySqlDbType.UInt32).Value = now;
					insertQry.ExecuteNonQuery();
				}
				resp.Respond("Ressourcenkoloübersicht eingelesen!\n");
			}
		}
	}
	class RessourcenKoloÜbersichtTeil2Parser : ReportParser {
        public RessourcenKoloÜbersichtTeil2Parser(NewscanHandler handler)
            : base(handler, false) {
            String dot = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator;
            String comma = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator;
            Requires(typeof(RessourcenKoloÜbersichtParser));
            AddPattern(@"Ressourcenkoloübersicht\s+Teil\s2\s+
Kolonie\s+FP\s+Credits\s+Steuersatz\s+Bevölkerung\s+Zufr\s+
([\s\S]+?)
Gesamt\s+[\d" + dot+@"]+\s+\(\S+?\*\((\d+,\d+)\+(\d+,\d+)\)\)\s+([\d"+dot+comma+@"]+)\s+\([^)]+\)\sAllisteuer:\s+([\d"+dot+comma+@"]+)", PatternFlags.All);
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match outerMatch in matches) {
				uint creditsGes = (uint)Math.Round(float.Parse(outerMatch.Groups[4].Value));
                float globalFpMod = float.Parse(outerMatch.Groups[2].Value)+float.Parse(outerMatch.Groups[3].Value);
                String dot = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator;
                String comma = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator;
				MatchCollection c = Regex.Matches(outerMatch.Groups[1].Value, KolonieName + @"\s+(\d+):(\d+):(\d+)\s+([\d"+dot+comma+@"]+)\s+
\([^)]+\)\)\s+(\S+)\s+\d+%\s+\S+\s+/\s+(\S+)\s+/\s+\S+\s+
\(([^)]+)\)\s+(?:(\S+)\s+
\(([^)]+)\))?", RegexOptions.IgnorePatternWhitespace);
                if (c.Count == 0)
                    return;
				int planiCnt = c.Count;
				float creditsAllysteuer = float.Parse(outerMatch.Groups[5].Value) / planiCnt;
				foreach (Match innerMatch in c) {
					uint gala = uint.Parse(innerMatch.Groups[1].Value);
					uint sys = uint.Parse(innerMatch.Groups[2].Value);
					uint pla = uint.Parse(innerMatch.Groups[3].Value);
                    float fp = globalFpMod*float.Parse(innerMatch.Groups[4].Value);
					float creds = float.Parse(innerMatch.Groups[5].Value);
					uint bev = uint.Parse(innerMatch.Groups[6].Value, System.Globalization.NumberStyles.Integer|System.Globalization.NumberStyles.AllowThousands);
                    int vBev = int.Parse(innerMatch.Groups[7].Value, System.Globalization.NumberStyles.Integer | System.Globalization.NumberStyles.AllowThousands);
					float zu, vZu;
					if (innerMatch.Groups[8].Length > 0) {
						zu = float.Parse(innerMatch.Groups[8].Value);
						vZu = float.Parse(innerMatch.Groups[9].Value);
					} else {
						zu = 100;
						vZu = 0;
					}

					uint insertFP = (uint)Math.Round(fp * 100);
					int realCreds = (int)Math.Round((creds + creditsAllysteuer) * 100);
					uint realZu = (uint)Math.Round(zu * 100);
					int realVZu = (int)Math.Round(vZu * 100);

					MySqlCommand planIdQry = new MySqlCommand("SELECT id from " + DBPrefix + "universum WHERE sys=?sys AND gala=?gala AND pla=?pla", con);
					planIdQry.Parameters.Add("?sys", MySqlDbType.UInt32).Value = sys;
					planIdQry.Parameters.Add("?gala", MySqlDbType.UInt32).Value = gala;
					planIdQry.Parameters.Add("?pla", MySqlDbType.UInt32).Value = pla;
					object res = planIdQry.ExecuteScalar();
					if (res == null || Convert.IsDBNull(res)) {
						resp.RespondError("Universumsdaten fehlerhaft oder unvollständig!\n");
						continue;
					}
					uint planid = (uint)res;
					MySqlCommand updateQry = new MySqlCommand("UPDATE " + DBPrefix + @"ressuebersicht
SET fp=?fp, cr=?cr, bev=?bev, zu=?zu, vCr=?vCr, vBev=?vBev, vZu=?vZu
WHERE planid=?planid", con);
					updateQry.Parameters.Add("?fp", MySqlDbType.UInt32).Value = insertFP;
					updateQry.Parameters.Add("?cr", MySqlDbType.UInt32).Value = creditsGes;
					updateQry.Parameters.Add("?bev", MySqlDbType.UInt32).Value = bev;
					updateQry.Parameters.Add("?zu", MySqlDbType.UInt32).Value = realZu;
					updateQry.Parameters.Add("?vCr", MySqlDbType.Int32).Value = realCreds;
					updateQry.Parameters.Add("?vBev", MySqlDbType.Int32).Value = vBev;
					updateQry.Parameters.Add("?vZu", MySqlDbType.Int32).Value = realVZu;
					updateQry.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
					updateQry.ExecuteNonQuery();
				}
				resp.Respond("RessourcenKoloÜbersicht Teil 2 eingelesen!\n");
			}
		}
	}
}
