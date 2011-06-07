using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Xml;

namespace IWDB.Parser {
	class KBParser:ReportParser {
		public KBParser(NewscanHandler newscanHandler)
			: base(newscanHandler) {
				AddPatern(@"http://www\.icewars\.de/portal/kb/(de|en)/kb\.php\?id=(\d+)&md_hash=([a-z0-9A-Z]{32})");
		}
		public override void Matched(System.Text.RegularExpressions.MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach(Match match in matches) {
				String url = match.Value + "&typ=xml";
				XmlNode xml = IWCache.Query(url, con, DBPrefix);
				xml = xml["kampf"];
				ResourceSet pluenderung = new ResourceSet();
				ResourceSet attresslost = new ResourceSet();
				ResourceSet defresslost = new ResourceSet();

				XmlNode ressverluste = xml["resverluste"];
				if(ressverluste != null) {
					if(ressverluste["att"] != null)
						attresslost.ParseXmlKb(ressverluste["att"]);
					if(ressverluste["def"] != null)
						defresslost.ParseXmlKb(ressverluste["def"]);
				}

				XmlNode pluenderungXml = xml["pluenderung"];
				if(pluenderungXml != null && pluenderungXml.HasChildNodes) {
					pluenderung.ParseXmlKb(pluenderungXml);
				}

				MySql.Data.MySqlClient.MySqlCommand cmd = new MySql.Data.MySqlClient.MySqlCommand(@"INSERT IGNORE INTO " + DBPrefix + @"raidberichte (iwid, hash, time, angreifer, angrAlly, verteidiger, verteidigerAlly, score, rFe, rSt, rCh, rVv, rEi, rWa, rEn, zFe, zSt, zCh, zVv, zEi, zWa, zEn) VALUES (?iwid, ?hash, ?time, ?angreifer, ?angrAlly, ?verteidiger, ?verteidigerAlly, ?score, ?rFe, ?rSt, ?rCh, ?rVv, ?rEi, ?rWa, ?rEn, ?zFe, ?zSt, ?zCh, ?zVv, ?zEi, ?zWa, ?zEn)", con);
				cmd.Parameters.Add("?iwid", MySql.Data.MySqlClient.MySqlDbType.UInt32).Value = int.Parse(match.Groups[2].Value);
				cmd.Parameters.Add("?hash", MySql.Data.MySqlClient.MySqlDbType.String).Value = match.Groups[3].Value;
				cmd.Parameters.Add("?time", MySql.Data.MySqlClient.MySqlDbType.UInt32).Value = uint.Parse(xml.SelectSingleNode("timestamp").Attributes["value"].InnerText);
				cmd.Parameters.Add("?angreifer", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("flotten_att/user/name").Attributes["value"].InnerText;
				cmd.Parameters.Add("?angrAlly", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("flotten_att/user/allianz_tag").Attributes["value"].InnerText;
				cmd.Parameters.Add("?verteidiger", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("pla_def/user/name").Attributes["value"].InnerText;
				cmd.Parameters.Add("?verteidigerAlly", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("pla_def/user/allianz_tag").Attributes["value"].InnerText;
				pluenderung -= attresslost;
				cmd.Parameters.Add("?score", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.RaidScore;
				cmd.Parameters.Add("?rFe", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Eisen;
				cmd.Parameters.Add("?rSt", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Stahl;
				cmd.Parameters.Add("?rCh", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Chemie;
				cmd.Parameters.Add("?rVv", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.VV4A;
				cmd.Parameters.Add("?rEi", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Eis;
				cmd.Parameters.Add("?rWa", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Wasser;
				cmd.Parameters.Add("?rEn", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = pluenderung.Energie;
				cmd.Parameters.Add("?zFe", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Eisen;
				cmd.Parameters.Add("?zSt", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Stahl;
				cmd.Parameters.Add("?zCh", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Chemie;
				cmd.Parameters.Add("?zVv", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.VV4A;
				cmd.Parameters.Add("?zEi", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Eis;
				cmd.Parameters.Add("?zWa", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Wasser;
				cmd.Parameters.Add("?zEn", MySql.Data.MySqlClient.MySqlDbType.Int32).Value = defresslost.Energie;

				cmd.Prepare();
				if(cmd.ExecuteNonQuery() == 0) {
					resp.Respond("Kampfbericht übersprungen!");
				} else {
					resp.Respond("Kampfbericht eingelesen!");
				}
			}
		}
	}
}
