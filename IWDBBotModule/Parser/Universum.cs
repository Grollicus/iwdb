using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using System.Globalization;
using System.Xml;

namespace IWDB.Parser {
    abstract class UniXmlParser:ReportParser {
        public UniXmlParser(NewscanHandler h)
            : base(h) {}
        public void Parse(List<XmlNode> xmls, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            MySqlCommand checkQuery = new MySqlCommand(@"SELECT count(*) FROM " + DBPrefix + "universum WHERE gala=?gal AND sys=?sys AND pla=?pla AND inserttime < ?time", con);
            checkQuery.Parameters.Add("?gal", MySqlDbType.UInt32);
            checkQuery.Parameters.Add("?sys", MySqlDbType.UInt32);
            checkQuery.Parameters.Add("?pla", MySqlDbType.UInt32);
            checkQuery.Parameters.Add("?time", MySqlDbType.UInt32);
            checkQuery.Prepare();
            MySqlCommand allyTagInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "uni_userdata (name, allytag, updatetime) VALUES (?name, ?tag, ?time) ON DUPLICATE KEY UPDATE allytag=IF(VALUES(updatetime) > updatetime, VALUES(allytag), allytag), updatetime=IF(VALUES(updatetime) > updatetime, VALUES(updatetime), updatetime)", con);
            allyTagInsert.Parameters.Add("?name", MySqlDbType.String);
            allyTagInsert.Parameters.Add("?tag", MySqlDbType.String);
            allyTagInsert.Parameters.Add("?time", MySqlDbType.UInt32);
            allyTagInsert.Prepare();
            MySqlCommand insertQry = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + "universum (iwid, gala, sys, pla, inserttime, planityp, objekttyp, ownername, planiname) VALUES (?iwid, ?gala, ?sys, ?pla, ?time, ?ptyp, ?otyp, ?oname, ?pname)", con);
            insertQry.Parameters.Add("?iwid", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?gala", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?sys", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?pla", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?time", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?ptyp", MySqlDbType.Enum);
            insertQry.Parameters.Add("?otyp", MySqlDbType.Enum);
            insertQry.Parameters.Add("?oname", MySqlDbType.String);
            insertQry.Parameters.Add("?pname", MySqlDbType.String);
            insertQry.Prepare();
            MySqlCommand updateQry = new MySqlCommand("UPDATE " + DBPrefix + "universum SET iwid=?iwid, planityp=?ptyp, objekttyp=?otyp, ownername=?oname, planiname=?pname, inserttime=?time WHERE gala=?gala AND sys=?sys AND pla=?pla", con);
            updateQry.Parameters.Add("?iwid", MySqlDbType.UInt32);
            updateQry.Parameters.Add("?gala", MySqlDbType.UInt32);
            updateQry.Parameters.Add("?sys", MySqlDbType.UInt32);
            updateQry.Parameters.Add("?pla", MySqlDbType.UInt32);
            updateQry.Parameters.Add("?ptyp", MySqlDbType.Enum);
            updateQry.Parameters.Add("?otyp", MySqlDbType.Enum);
            updateQry.Parameters.Add("?oname", MySqlDbType.String);
            updateQry.Parameters.Add("?pname", MySqlDbType.String);
            updateQry.Parameters.Add("?time", MySqlDbType.UInt32);
            updateQry.Prepare();
            uint insert = 0;
            uint update = 0;
            foreach (XmlNode xml in xmls) {
                uint age = uint.Parse(xml.SelectSingleNode("planeten_data/informationen/aktualisierungszeit").InnerText);
                List<UniXmlPlani> planis = new List<UniXmlPlani>();
                foreach (XmlNode n in xml.SelectNodes("planeten_data/planet")) {
                    UniXmlPlani plani = new UniXmlPlani(n);
                    if (plani.planiTyp == "Sonne")
                        continue;
                    checkQuery.Parameters["?gal"].Value = plani.gala;
                    checkQuery.Parameters["?sys"].Value = plani.sys;
                    checkQuery.Parameters["?pla"].Value = plani.pla;
                    checkQuery.Parameters["?time"].Value = age;
                    if (plani.ownerName.Length > 0) {
                        allyTagInsert.Parameters["?name"].Value = plani.ownerName;
                        allyTagInsert.Parameters["?tag"].Value = plani.allyTag;
                        allyTagInsert.Parameters["?time"].Value = age;
                        allyTagInsert.ExecuteNonQuery();
                    }
                    if ((Int64)checkQuery.ExecuteScalar() == 0) {
                        insertQry.Parameters["?iwid"].Value = plani.iwid;
                        insertQry.Parameters["?gala"].Value = plani.gala;
                        insertQry.Parameters["?sys"].Value = plani.sys;
                        insertQry.Parameters["?pla"].Value = plani.pla;
                        insertQry.Parameters["?time"].Value = age;
                        insertQry.Parameters["?ptyp"].Value = plani.planiTyp;
                        insertQry.Parameters["?otyp"].Value = plani.objektTyp;
                        insertQry.Parameters["?oname"].Value = plani.ownerName;
                        insertQry.Parameters["?pname"].Value = plani.planiName;
                        insertQry.ExecuteNonQuery();
                        ++insert;
                    } else {
                        updateQry.Parameters["?iwid"].Value = plani.iwid;
                        updateQry.Parameters["?gala"].Value = plani.gala;
                        updateQry.Parameters["?sys"].Value = plani.sys;
                        updateQry.Parameters["?pla"].Value = plani.pla;
                        updateQry.Parameters["?time"].Value = age;
                        updateQry.Parameters["?ptyp"].Value = plani.planiTyp;
                        updateQry.Parameters["?otyp"].Value = plani.objektTyp;
                        updateQry.Parameters["?oname"].Value = plani.ownerName;
                        updateQry.Parameters["?pname"].Value = plani.planiName;
                        updateQry.ExecuteNonQuery();
                        ++update;
                    }
                }
            }
            resp.Respond(insert + " neue Planeten eingelesen und " + update + " aktualisiert!");
        }
    }
    class UniXMLUniversumsParser : UniXmlParser {
        public UniXMLUniversumsParser(NewscanHandler h)
            : base(h) {
            AddPatern("<\\?xml[^>]+?>\\s+<planeten_data>[\\s\\S]+?</planeten_data>");
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            List<XmlNode> xmls = new List<XmlNode>();
            foreach (Match m in matches) {
                XmlDocument doc = new XmlDocument();
                doc.LoadXml(m.Groups[0].Value);
                xmls.Add(doc);
            }
            resp.Respond(matches.Count + "x Uni-XML-Daten erkannt!");
            Parse(xmls, posterID, victimID, con, handler, resp);
        }
    }

    class UniXMLLinkParser:UniXmlParser {
		public UniXMLLinkParser(NewscanHandler h)
			: base(h) {
            base.AddPatern(@"http://www.icewars.de/xml/user_univ_scan/[a-f0-9]{32}\.xml", PatternFlags.All);
		}
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			List<XmlNode> xmls = new List<XmlNode>();
			foreach(Match linkMatch in matches) {
				XmlNode xml = IWCache.Query(linkMatch.Value, con, DBPrefix);
				xmls.Add(xml);
			}
            resp.Respond(matches.Count + "x Unixml-Links erkannt!");
            base.Parse(xmls, posterID, victimID, con, handler, resp);
		}
	}
	class UniXmlPlani {
		public readonly uint iwid;
		public readonly uint gala;
		public readonly uint sys;
		public readonly uint pla;
		public readonly String planiTyp;
		public readonly String objektTyp;
		public readonly String ownerName;
		public readonly String planiName;
        public readonly String allyTag;
		public UniXmlPlani(XmlNode n) {
			iwid = getUInt(n, "id");
			gala = getUInt(n, "koordinaten/gal");
			sys = getUInt(n, "koordinaten/sol");
			pla = getUInt(n, "koordinaten/pla");
			planiTyp = getValue(n, "planet_typ");
			objektTyp = getValue(n, "objekt_typ");
			ownerName = getValue(n, "user/name");
            allyTag = getValue(n, "user/allianz_tag");
            planiName = getValue(n, "name");
		}
		protected static String getValue(XmlNode n, String name) {
			return n.SelectSingleNode(name).InnerText;
		}
		protected uint getUInt(XmlNode n, String name) {
			return uint.Parse(getValue(n, name));
		}
	}
}