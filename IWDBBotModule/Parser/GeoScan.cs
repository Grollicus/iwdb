using System;
using System.Collections.Generic;
using System.Text;
using System.Xml;
using System.Net;
using System.IO;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using System.Globalization;
using System.Linq;

namespace IWDB.Parser {
	class BesonderheitenData {
		public readonly Dictionary<String, int> besFlags;
		public readonly Dictionary<String, int> nebelIDs;
		String DBPrefix;
		public BesonderheitenData(MySqlConnection con, String DBPrefix) {
			besFlags = new Dictionary<string, int>();
			nebelIDs = new Dictionary<string, int>();
			this.DBPrefix = DBPrefix;

			MySqlCommand qry = new MySqlCommand("SELECT ID, Name FROM " + DBPrefix + "besonderheiten", con);
			MySqlDataReader r = qry.ExecuteReader();
			try {
				while (r.Read()) {
					besFlags.Add(r.GetString(1), r.GetInt32(0));
				}
			} finally {
				r.Close();
			}
			qry = new MySqlCommand("SELECT ID, Name FROM " + DBPrefix + "nebel", con);
			r = qry.ExecuteReader();
			try {
				while (r.Read()) {
					nebelIDs.Add(r.GetString(1), r.GetInt32(0));
				}
			} finally {
				r.Close();
			}
			/*
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("alte Ruinen", 1);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("Asteroidengürtel", 2);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("instabiler Kern", 4);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("Gold", 8);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("Natürliche Quelle", 16);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("planetarer Ring", 32);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("radioaktiv", 64);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("toxisch", 128);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("Ureinwohner", 256);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("wenig Rohstoffe", 512);
			INSERT INTO iwdb_besonderheiten (Name, ID) VALUES ("Mond", 1024);
			*/
		}
        public List<String> BesonderheitenDecode(int bes) {
            List<String> ret = new List<string>();
            foreach (KeyValuePair<String, int> p in besFlags.Where(p => (p.Value & bes) != 0)) {
                ret.Add(p.Key);
            }
            return ret;
        }
        public String NebelDecode(int nebel) {
            return nebel == 0 ? "" : besFlags.First(p => (p.Value & nebel) != 0).Key;
        }
        public int NebelEncode(String nebel) {
            return nebelIDs.ContainsKey(nebel) ? nebelIDs[nebel] : 0;
        }
        public int BesonderheitenEncode(List<String> bes) {
            int ret = 0;
            bes.ForEach(s => ret += besFlags.ContainsKey(s) ? besFlags[s] : 0);
            return ret;
        }
    }
	class Besonderheiten {
		BesonderheitenData dta;
		int bes;
		int nebel;
		public Besonderheiten(BesonderheitenData dta) {
			this.bes = 0;
			this.nebel = 0;
			this.dta = dta;
		}
		public void Add(String besonderheit) {
			if (dta.besFlags.ContainsKey(besonderheit))
				this.bes += dta.besFlags[besonderheit];
			else if (dta.nebelIDs.ContainsKey(besonderheit))
				this.nebel = dta.nebelIDs[besonderheit];
			else {
				Console.WriteLine("Besonderheit " + besonderheit + " unbekannt!");
				throw new Exception("Besonderheit " + besonderheit + " unbekannt!");
			}
		}
		public Int32 BesFlags { get { return bes; } }
		public Int32 NebelID { get { return nebel; } }
	}
	class ScanLinkParser : ReportParser {
        public ScanLinkParser(NewscanHandler newscanHandler) : base(newscanHandler) { AddPatern(@"http://www\.icewars\.de/portal/kb/(de|en)/sb\.php\?id=(\d+)&md_hash=([a-z0-9A-Z]{32})"); }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match m in matches) {
				String url = m.Value+"&typ=xml";
				XmlNode xml = IWCache.Query(url, con, DBPrefix);
				if (xml.SelectSingleNode("scann/informationen/vollstaendig").InnerText != "1") {
					resp.Respond("Ein Scan war nicht vollständig!");
					continue;
				}
				switch (xml.SelectSingleNode("scann/scann_typ/id").InnerText) {
					case "1": //Sondierung (Geologie)
						GeoScan scan = GeoScan.Parse(xml, DBPrefix, handler.BesData);
						if (scan == null) {
							resp.RespondError("Beim Einlesen eines Geoscans ist ein Fehler aufgetreten!\n");
							continue;
						}
						scan.ToDB(con);
						resp.Respond("Geoscan eingelesen!\n");
						break;
					case "2": //Sondierung (Gebäude/Ress)
						GebScan s = new GebScan();
						s.Load(xml, uint.Parse(m.Groups[2].Value), m.Groups[3].Value, con, DBPrefix);
						if (s.ToDB(con, DBPrefix)) {
							resp.Respond("Gebäudescan eingelesen!\n");
						} else {
							resp.Respond("Gebäudescan übersprungen!\n");
						}
						break;
                    case "3": //Sondierung (Schiffe/Def/Ress)
                        SchiffScan schiffScan = new SchiffScan();
                        schiffScan.Load(xml, uint.Parse(m.Groups[2].Value), m.Groups[3].Value, con, DBPrefix);
                        if (schiffScan.ToDB(con, DBPrefix)) {
                            resp.Respond("Schiffscan eingelesen!\n");
                        } else {
                            resp.Respond("Schiffscan übersprungen!\n");
                        }
                        break;
				}
			}
		}
	}

	class SchiffScanFlotte {
		String OwnerName;
		String Typ;
		Dictionary<uint, uint> Schiffe;
		MySqlConnection con;
		String DBPrefix;
		public SchiffScanFlotte(MySqlConnection con, String DBPrefix) {
			this.Schiffe = new Dictionary<uint, uint>();
			this.con = con;
			this.DBPrefix = DBPrefix;
		}
		public void Parse(String str, String ownerName, String typ) {
            this.OwnerName = ownerName.Replace("|", "");
            this.Typ = typ;
			MatchCollection matches = Regex.Matches(str, @"^([\s\S]+?)\s+(\d+)$", RegexOptions.Multiline);
			foreach (Match m in matches) {
				Schiffe.Add(getSchiffsID(m.Groups[1].Value.Replace("|", "")), uint.Parse(m.Groups[2].Value));
			}
		}
        public void Load(XmlNode xml, bool stationiert) {
            this.OwnerName = xml.SelectSingleNode("name").InnerText;
            this.Typ = stationiert ? "stationiert" : "planetar";
            foreach (XmlNode n in xml.SelectNodes("schiffe/schifftyp")) {
                Schiffe.Add(getSchiffsID(n["name"].InnerText), uint.Parse(n["anzahl"].InnerText));
            }
            foreach (XmlNode n in xml.SelectNodes("defence/defencetyp")) {
                Schiffe.Add(getSchiffsID(n["name"].InnerText), uint.Parse(n["anzahl"].InnerText));
            }
        }
		public void ToDB(uint scanid) {
			MySqlCommand flInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "scans_flotten (scanid, owner, typ) VALUES (?scanid, ?owner, ?typ)", con);
            flInsert.Parameters.Add("?scanid", MySqlDbType.String).Value = scanid;
			flInsert.Parameters.Add("?owner", MySqlDbType.String).Value = OwnerName;
			flInsert.Parameters.Add("?typ", MySqlDbType.String).Value = Typ;
			flInsert.ExecuteNonQuery();
			long flid = flInsert.LastInsertedId;
			MySqlCommand schiffsInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "scans_flotten_schiffe (flid, schid, anz) VALUES (?flid, ?schid, ?anz)", con);
			schiffsInsert.Parameters.Add("?flid", MySqlDbType.UInt32).Value = flid;
			schiffsInsert.Parameters.Add("?schid", MySqlDbType.UInt32);
			schiffsInsert.Parameters.Add("?anz", MySqlDbType.UInt32);
			schiffsInsert.Prepare();
			foreach (KeyValuePair<uint, uint> p in Schiffe) {
				schiffsInsert.Parameters["?schid"].Value = p.Key;
				schiffsInsert.Parameters["?anz"].Value = p.Value;
				schiffsInsert.ExecuteNonQuery();
			}
		}
		protected uint getSchiffsID(String Name) {
			MySqlCommand insertCmd = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + "schiffe (name) VALUES (?name)", con);
            insertCmd.Parameters.Add("?name", MySqlDbType.String).Value = Name;
			insertCmd.ExecuteNonQuery();
			if (insertCmd.LastInsertedId != 0)
				return (uint)insertCmd.LastInsertedId;
			MySqlCommand idQry = new MySqlCommand("SELECT id FROM " + DBPrefix + "schiffe WHERE name=?name", con);
			idQry.Parameters.Add("?name", MySqlDbType.String).Value = Name;
			return Convert.ToUInt32(idQry.ExecuteScalar());
		}
	}

	class GeoScan {
		public int gal;
		public int sol;
		public int pla;
		public String pla_typ;
		public String obj_typ;
		public int eisen, chem, eis;
		public int tt_eisen, tt_chem, tt_eis;
		public int lbled, gravi;
		public int bev_max;
		public Besonderheiten besonderheiten;
		public int mod_forschung;
		public int mod_geb_k, mod_geb_d;
		public int mod_schif_k, mod_schif_d;
		public uint timestamp;
        public uint resetTimestamp;
        public bool hasMods;
        public String owner_name;
        public String owner_ally;

		protected String DBPrefix;
		/*IDs:
		 *	1 - Eisen
		 *	4 - Eis
		 *	5 - Chem.
		*/


		public static GeoScan Parse(XmlNode n, String DBPrefix, BesonderheitenData dta) {
			try {
				GeoScan s = new GeoScan(DBPrefix);
				NumberFormatInfo numberFormat = new NumberFormatInfo();
				numberFormat.NumberDecimalSeparator = ".";
				numberFormat.NumberGroupSeparator = ",";
				
				XmlNode planidata = n.SelectSingleNode("scann/plani_data");
				XmlNode coords = planidata.SelectSingleNode("koordinaten");
				s.gal = int.Parse(coords["gal"].InnerText);
				s.sol = int.Parse(coords["sol"].InnerText);
				s.pla = int.Parse(coords["pla"].InnerText);
				s.pla_typ = planidata.SelectSingleNode("planeten_typ/name").InnerText;
				s.obj_typ = planidata.SelectSingleNode("objekt_typ/name").InnerText;
                s.owner_name = planidata.SelectSingleNode("user/name").InnerText;
                s.owner_ally = planidata.SelectSingleNode("user/allianz_tag").InnerText;
				XmlNode ressvorkommen = planidata.SelectSingleNode("ressourcen_vorkommen");
				foreach (XmlNode node in ressvorkommen.SelectNodes("ressource")) {
					switch (int.Parse(node["id"].InnerText)) {
						case 1:
							s.eisen = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
						case 4:
							s.eis = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
						case 5:
							s.chem = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
					}
				}
				foreach (XmlNode node in ressvorkommen.SelectNodes("ressource_tech_team")) {
					switch (int.Parse(node["id"].InnerText)) {
						case 1:
							s.tt_eisen = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
						case 4:
							s.tt_eis = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
						case 5:
							s.tt_chem = Convert.ToInt32(float.Parse(node["wert"].InnerText, numberFormat) * 1000);
							break;
					}
				}
				s.gravi = Convert.ToInt32(float.Parse(planidata["gravitation"].InnerText, numberFormat) * 100);
				s.lbled = Convert.ToInt32(float.Parse(planidata["lebensbedingungen"].InnerText, numberFormat) * 1000);
				s.bev_max = int.Parse(planidata["bev_max"].InnerText);

				s.besonderheiten = new Besonderheiten(dta);
				foreach (XmlNode node in planidata.SelectNodes("besonderheiten/besonderheit/name"))
					s.besonderheiten.Add(node.InnerText);
                s.timestamp = uint.Parse(n.SelectSingleNode("scann/timestamp").InnerText);
                XmlNode mod = planidata.SelectSingleNode("modifikatoren");
                s.hasMods = mod != null;
                if (mod != null) {
                    s.mod_forschung = (int)(float.Parse(mod["forschung"].InnerText, numberFormat) * 100);
                    s.mod_geb_k = (int)(float.Parse(mod["gebaeude_bau"]["kosten"].InnerText, numberFormat) * 100);
                    s.mod_geb_d = (int)(float.Parse(mod["gebaeude_bau"]["dauer"].InnerText, numberFormat) * 100);
                    s.mod_schif_k = (int)(float.Parse(mod["schiff_bau"]["kosten"].InnerText, numberFormat) * 100);
                    s.mod_schif_d = (int)(float.Parse(mod["schiff_bau"]["dauer"].InnerText, numberFormat) * 100);
                    //Die Angabe im Scan ist relativ ab dem Timestamp, +86400 Sekunden weil +-24h
                    s.resetTimestamp = uint.Parse(n.SelectSingleNode("scann/plani_data/reset_timestamp").InnerText) + s.timestamp + 86400;
                }
                return s;
			} catch (Exception) {
                return null;
			}
		}
		public GeoScan(String DBPrefix) {
			this.DBPrefix = DBPrefix;
		}
		public void ToDB(MySqlConnection con) {
			MySqlCommand cmd = new MySqlCommand("SELECT universum.ID, geoscans.timestamp, universum.inserttime FROM (" + DBPrefix + @"universum AS universum) LEFT JOIN (" + DBPrefix + @"geoscans AS geoscans)
				ON universum.ID = geoscans.ID
				WHERE sys=?sys AND gala=?gal AND pla=?pla", con);
			cmd.Parameters.Add("?sys", MySqlDbType.UInt16).Value = sol;
			cmd.Parameters.Add("?gal", MySqlDbType.UInt16).Value = gal;
			cmd.Parameters.Add("?pla", MySqlDbType.UInt16).Value = pla;
			cmd.Prepare();
			MySqlDataReader r = cmd.ExecuteReader(System.Data.CommandBehavior.SingleRow);
			uint old_geo_timestamp = 0;
			uint old_timestamp = 0;
			int id;
			if(r.Read()) {
				id = r.GetInt32(0);
				old_geo_timestamp = r.IsDBNull(1) ? 0 : r.GetUInt32(1);
				old_timestamp = r.GetUInt32(2);
				r.Close();
			} else {
				r.Close();
				MySqlCommand uniInsert = new MySqlCommand(@"INSERT INTO " + DBPrefix + @"universum 
	(gala, sys, pla, inserttime, planityp, objekttyp, ownername, planiname) 
	VALUES
	(?gal, ?sys, ?pla, ?inserttime, ?typ, ?objtyp, ?owner, ?name)", con);
				uniInsert.Parameters.Add("?gal", MySqlDbType.UInt16).Value = gal;
				uniInsert.Parameters.Add("?sys", MySqlDbType.UInt16).Value = sol;
				uniInsert.Parameters.Add("?pla", MySqlDbType.UInt16).Value = pla;
				uniInsert.Parameters.Add("?inserttime", MySqlDbType.UInt32).Value = timestamp;
				uniInsert.Parameters.Add("?typ", MySqlDbType.String).Value = pla_typ;
				uniInsert.Parameters.Add("?objtyp", MySqlDbType.String).Value = obj_typ;
				uniInsert.Parameters.Add("?owner", MySqlDbType.String).Value = owner_name;
				uniInsert.Parameters.Add("?name", MySqlDbType.String).Value = "?";
				uniInsert.Prepare();
				uniInsert.ExecuteNonQuery();
				id = (int)uniInsert.LastInsertedId;
			}
			if(old_timestamp < this.timestamp) {
				MySqlCommand uniUpdate = new MySqlCommand(@"UPDATE " + DBPrefix + @"universum SET inserttime=?inserttime, planityp=?typ, objekttyp=?objtyp, ownername=?owner, planiname=?name WHERE ID=?id", con);
				uniUpdate.Parameters.Add("?inserttime", MySqlDbType.UInt32).Value = timestamp;
				uniUpdate.Parameters.Add("?typ", MySqlDbType.String).Value = pla_typ;
				uniUpdate.Parameters.Add("?objtyp", MySqlDbType.String).Value = obj_typ;
				uniUpdate.Parameters.Add("?owner", MySqlDbType.String).Value = owner_name;
				uniUpdate.Parameters.Add("?name", MySqlDbType.String).Value = "?";
				uniUpdate.Parameters.Add("?id", MySqlDbType.UInt16).Value = id;
				uniUpdate.Prepare();
				uniUpdate.ExecuteNonQuery();
			}
            if (old_geo_timestamp < this.timestamp) {
                if (owner_name.Length>0) {
                    MySqlCommand allyUpd = new MySqlCommand("INSERT INTO " + DBPrefix + @"uni_userdata (name, allytag, updatetime) VALUES (?name, ?tag, ?time) ON DUPLICATE KEY UPDATE name=IF(updatetime<VALUES(updatetime),VALUES(name),name), allytag=IF(updatetime<VALUES(updatetime),VALUES(allytag),allytag), updatetime=IF(updatetime<VALUES(updatetime),VALUES(updatetime),updatetime)", con);
                    allyUpd.Parameters.Add("?name", MySqlDbType.String).Value = owner_name;
                    allyUpd.Parameters.Add("?tag", MySqlDbType.String).Value = owner_ally;
                    allyUpd.Parameters.Add("?time", MySqlDbType.UInt32).Value = timestamp;
                    allyUpd.ExecuteNonQuery();
                }

                MySqlCommand cmd2 = new MySqlCommand("INSERT INTO " + DBPrefix + @"geoscans (ID, eisen, chemie, eis, gravi, lbed, nebel, besonderheiten, fmod, gebmod, gebtimemod, shipmod, shiptimemod, tt_eisen, tt_chemie, tt_eis, timestamp, reset) VALUES (
				?id,?eisen,?chem,?eis,?gravi,?lbed,?nebel,?bes,?mod_forschung,?mod_geb_k,?mod_geb_d,?mod_schif_k,?mod_schif_d,?tt_eisen,?tt_chem,?tt_eis,?timestamp, ?resetTimestamp)
				ON DUPLICATE KEY UPDATE eisen=VALUES(eisen), chemie=VALUES(chemie), eis=VALUES(eis), gravi=VALUES(gravi),
				lbed=VALUES(lbed), nebel=VALUES(nebel), besonderheiten=VALUES(besonderheiten), fmod=VALUES(fmod),
				gebmod=VALUES(gebmod), gebtimemod=VALUES(gebtimemod), shipmod=VALUES(shipmod), shiptimemod=VALUES(shiptimemod),
				tt_eisen=VALUES(tt_eisen), tt_chemie=VALUES(tt_chemie), tt_eis=VALUES(tt_eis), timestamp=VALUES(timestamp), reset=VALUES(reset)", con);
                cmd2.Parameters.Add("?id", MySqlDbType.UInt32).Value = id;
                cmd2.Parameters.Add("?eisen", MySqlDbType.Int32).Value = eisen;
                cmd2.Parameters.Add("?chem", MySqlDbType.Int32).Value = chem;
                cmd2.Parameters.Add("?eis", MySqlDbType.Int32).Value = eis;
                cmd2.Parameters.Add("?gravi", MySqlDbType.Int32).Value = gravi;
                cmd2.Parameters.Add("?lbed", MySqlDbType.Int32).Value = lbled;
                cmd2.Parameters.Add("?nebel", MySqlDbType.Int32).Value = besonderheiten.NebelID;
                cmd2.Parameters.Add("?bes", MySqlDbType.Int32).Value = besonderheiten.BesFlags;
                if (hasMods) {
                    cmd2.Parameters.Add("?mod_forschung", MySqlDbType.Int32).Value = mod_forschung;
                    cmd2.Parameters.Add("?mod_geb_k", MySqlDbType.Int32).Value = mod_geb_k;
                    cmd2.Parameters.Add("?mod_geb_d", MySqlDbType.Int32).Value = mod_geb_d;
                    cmd2.Parameters.Add("?mod_schif_k", MySqlDbType.Int32).Value = mod_schif_k;
                    cmd2.Parameters.Add("?mod_schif_d", MySqlDbType.Int32).Value = mod_schif_d;
                    cmd2.Parameters.Add("?resetTimestamp", MySqlDbType.UInt32).Value = resetTimestamp;
                } else {
                    cmd2.Parameters.Add("?mod_forschung", MySqlDbType.Int32).Value = null;
                    cmd2.Parameters.Add("?mod_geb_k", MySqlDbType.Int32).Value = null;
                    cmd2.Parameters.Add("?mod_geb_d", MySqlDbType.Int32).Value = null;
                    cmd2.Parameters.Add("?mod_schif_k", MySqlDbType.Int32).Value = null;
                    cmd2.Parameters.Add("?mod_schif_d", MySqlDbType.Int32).Value = null;
                    cmd2.Parameters.Add("?resetTimestamp", MySqlDbType.UInt32).Value = null;
                }
                cmd2.Parameters.Add("?tt_eisen", MySqlDbType.Int32).Value = tt_eisen;
                cmd2.Parameters.Add("?tt_chem", MySqlDbType.Int32).Value = tt_chem;
                cmd2.Parameters.Add("?tt_eis", MySqlDbType.Int32).Value = tt_eis;
                cmd2.Parameters.Add("?timestamp", MySqlDbType.UInt32).Value = timestamp;
                cmd2.Prepare();
                cmd2.ExecuteNonQuery();
            }
		}
	}
	class GebScan:IWScan {
		protected Dictionary<uint, uint> gebs;

		public void Load(XmlNode xml, uint iwid, string iwhash, MySqlConnection con, String DBPrefix) {
            base.Load(xml, iwid, iwhash);
			MySqlCommand cmd = new MySqlCommand("SELECT ID FROM " + DBPrefix + "gebs WHERE name=?name", con);
			cmd.Parameters.Add("?name", MySqlDbType.String);
			cmd.Prepare();
			gebs = new Dictionary<uint, uint>();
			foreach (XmlNode n in xml.SelectNodes("scann/gebaeude/gebaeude")) {
				gebs.Add(getGebID(n.SelectSingleNode("name").InnerText, cmd, con, DBPrefix), uint.Parse(n.SelectSingleNode("anzahl").InnerText));
			}
		}
		public bool ToDB(MySqlConnection con, String DBPrefix) {
            long scanID = base.ToDB(con, DBPrefix, "geb");
			if (scanID == 0)
				return false;
			MySqlCommand gebInsert = new MySqlCommand("INSERT INTO " + DBPrefix + @"scans_gebs (scanid, gebid, anzahl) VALUES (?kbid, ?gebid, ?anz)", con);
			gebInsert.Parameters.Add("?kbid", MySqlDbType.UInt32).Value = scanID;
			gebInsert.Parameters.Add("?gebid", MySqlDbType.UInt32);
			gebInsert.Parameters.Add("?anz", MySqlDbType.UInt16);
			gebInsert.Prepare();
			foreach (KeyValuePair<uint, uint> geb in gebs) {
				gebInsert.Parameters["?gebid"].Value = geb.Key;
				gebInsert.Parameters["?anz"].Value = geb.Value;
				gebInsert.ExecuteNonQuery();
			}
			return true;
		}
		protected uint getGebID(String name, MySqlCommand idQry, MySqlConnection con, String DBPrefix) {
			idQry.Parameters["?name"].Value = name;
			object ret = idQry.ExecuteScalar();
			if (ret == null || Convert.IsDBNull(ret)) {
				MySqlCommand insertQry = new MySqlCommand("INSERT INTO " + DBPrefix + "gebs (name) VALUES (?name)", con);
				insertQry.Parameters.Add("?name", MySqlDbType.String).Value = name;
				insertQry.ExecuteNonQuery();
				return (uint)insertQry.LastInsertedId;
			}
			return (uint)ret;
		}
	}
    class SchiffScan : IWScan {
        List<SchiffScanFlotte> flotten;
        public void Load(XmlNode xml, uint iwid, string iwhash, MySqlConnection con, String DBPrefix) {
            base.Load(xml, iwid, iwhash);
            flotten = new List<SchiffScanFlotte>();
            SchiffScanFlotte fl = new SchiffScanFlotte(con, DBPrefix);
            fl.Load(xml.SelectSingleNode("scann/pla_def/user"), false);
            flotten.Add(fl);
            foreach (XmlNode n in xml.SelectNodes("scann/flotten_def/user")) {
                fl = new SchiffScanFlotte(con, DBPrefix);
                fl.Load(xml.SelectSingleNode("scann/pla_def/user"), false);
                flotten.Add(fl);
            }
        }
        public bool ToDB(MySqlConnection con, String DBPrefix) {
            uint scanid = (uint)base.ToDB(con, DBPrefix, "schiff");
            if (scanid == 0)
                return false;
            foreach (SchiffScanFlotte fl in flotten) {
                fl.ToDB(scanid);
            }
            return true;
        }
    }
    abstract class IWScan {
        protected uint iwid;
        protected String hash;
        protected uint gal;
        protected uint sol;
        protected uint pla;
        protected String pla_typ;
        protected String obj_typ;
        protected String owner_name;
        protected String owner_ally;
        protected uint timestamp;
        protected ResourceSet ress;

        protected void Load(XmlNode xml, uint iwid, string iwhash) {
            this.iwid = iwid;
            this.hash = iwhash;
            XmlNode planidata = xml.SelectSingleNode("scann/plani_data");
            XmlNode coords = planidata.SelectSingleNode("koordinaten");
            gal = uint.Parse(coords["gal"].InnerText);
            sol = uint.Parse(coords["sol"].InnerText);
            pla = uint.Parse(coords["pla"].InnerText);
            pla_typ = planidata.SelectSingleNode("planeten_typ/name").InnerText;
            obj_typ = planidata.SelectSingleNode("objekt_typ/name").InnerText;
            owner_name = planidata.SelectSingleNode("user/name").InnerText;
            owner_ally = planidata.SelectSingleNode("user/allianz_tag").InnerText;
            timestamp = uint.Parse(xml.SelectSingleNode("scann/timestamp").InnerText);
            ress = new ResourceSet();
            ress.ParseXml(xml.SelectSingleNode("scann/ressourcen"));
        }

        protected long ToDB(MySqlConnection con, String DBPrefix, String type) {
            MySqlCommand scanInsert = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + @"scans (
				iwid, iwhash, time, gala, sys, pla, typ, planityp, objekttyp, ownername, ownerally, fe, st, vv, ch, ei, wa, en
			) VALUES (
				?iwid, ?iwhash, ?time, ?gala, ?sys, ?pla, ?typ, ?planityp, ?objekttyp, ?ownername, ?ownerally, ?fe, ?st, ?vv, ?ch, ?ei, ?wa, ?en
			)", con);
            scanInsert.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
            scanInsert.Parameters.Add("?iwhash", MySqlDbType.String).Value = hash;
            scanInsert.Parameters.Add("?time", MySqlDbType.UInt32).Value = timestamp;
            scanInsert.Parameters.Add("?gala", MySqlDbType.UInt32).Value = gal;
            scanInsert.Parameters.Add("?sys", MySqlDbType.UInt32).Value = sol;
            scanInsert.Parameters.Add("?pla", MySqlDbType.UInt32).Value = pla;
            scanInsert.Parameters.Add("?typ", MySqlDbType.String).Value = "geb";
            scanInsert.Parameters.Add("?planityp", MySqlDbType.String).Value = pla_typ;
            scanInsert.Parameters.Add("?objekttyp", MySqlDbType.String).Value = obj_typ;
            scanInsert.Parameters.Add("?ownername", MySqlDbType.String).Value = owner_name;
            scanInsert.Parameters.Add("?ownerally", MySqlDbType.String).Value = owner_ally;
            scanInsert.Parameters.Add("?fe", MySqlDbType.UInt32).Value = (uint)(ress.Eisen);
            scanInsert.Parameters.Add("?st", MySqlDbType.UInt32).Value = (uint)(ress.Stahl);
            scanInsert.Parameters.Add("?vv", MySqlDbType.UInt32).Value = (uint)(ress.VV4A);
            scanInsert.Parameters.Add("?ch", MySqlDbType.UInt32).Value = (uint)(ress.Chemie);
            scanInsert.Parameters.Add("?ei", MySqlDbType.UInt32).Value = (uint)(ress.Eis);
            scanInsert.Parameters.Add("?wa", MySqlDbType.UInt32).Value = (uint)(ress.Wasser);
            scanInsert.Parameters.Add("?en", MySqlDbType.UInt32).Value = (uint)(ress.Energie);
            scanInsert.ExecuteNonQuery();
            long scanID = scanInsert.LastInsertedId;
            if (scanID == 0)
                return scanID;

            PlaniIDFetcher f = new PlaniIDFetcher(KnownData.Owner, con, DBPrefix);
            uint planid = f.GetID(gal, sol, pla, null, owner_name);

            MySqlCommand dateQry = new MySqlCommand(@"SELECT scans.time
	FROM " + DBPrefix + @"lastest_scans AS lastest_scans LEFT JOIN " + DBPrefix + @"scans AS scans ON lastest_scans.scanid=scans.id
	WHERE lastest_scans.planid=?planid AND lastest_scans.typ=?type", con);
            dateQry.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
            dateQry.Parameters.Add("?type", MySqlDbType.String).Value = type;
            MySqlDataReader r = dateQry.ExecuteReader();
            if (!r.Read()) {
                r.Close();
                MySqlCommand lastestScanInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "lastest_scans(planid, typ, scanid) VALUES (?planid, ?type, ?scanid)", con);
                lastestScanInsert.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
                lastestScanInsert.Parameters.Add("?scanid", MySqlDbType.UInt32).Value = scanID;
                lastestScanInsert.Parameters.Add("?type", MySqlDbType.String).Value = type;
                lastestScanInsert.ExecuteNonQuery();
            } else if (r.GetUInt32(0) < timestamp) {
                r.Close();
                MySqlCommand lastestScanUpdate = new MySqlCommand("UPDATE " + DBPrefix + "lastest_scans SET scanid=?scanid WHERE planid=?planid AND typ=?type", con);
                lastestScanUpdate.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
                lastestScanUpdate.Parameters.Add("?type", MySqlDbType.String).Value = type;
                lastestScanUpdate.Parameters.Add("?scanid", MySqlDbType.UInt32).Value = scanID;
            }

            return scanID;
        }
    }
}


