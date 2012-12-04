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
		WarFilter warFilter;
        public ScanLinkParser(NewscanHandler newscanHandler, WarFilter warFilter) : base(newscanHandler) {
            AddPattern(@"http://www\.icewars\.de/portal/kb/de/sb\.php\?id=(\d+)&md_hash=([a-z0-9A-Z]{32})", "kb/de/sb.php", PatternFlags.All);
			this.warFilter = warFilter;
		}
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, DateTime now, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
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
						GebScan s = new GebScan(uint.Parse(m.Groups[1].Value), m.Groups[2].Value);
						s.LoadXml(xml, warFilter);
						if (s.ToDB(con, DBPrefix, warFilter.TechKostenCache)) {
							resp.Respond("Gebäudescan eingelesen!\n");
						} else {
							resp.Respond("Gebäudescan übersprungen!\n");
						}
						break;
                    case "3": //Sondierung (Schiffe/Def/Ress)
                        SchiffScan schiffScan = new SchiffScan(uint.Parse(m.Groups[1].Value), m.Groups[2].Value);
						schiffScan.LoadXml(xml, warFilter);
						if(schiffScan.ToDB(con, DBPrefix, warFilter.TechKostenCache)) {
                            resp.Respond("Schiffscan eingelesen!\n");
                        } else {
                            resp.Respond("Schiffscan übersprungen!\n");
                        }
                        break;
				}
			}
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
			} catch (Exception e) {
                IRCeX.Log.WriteLine(IRCeX.LogLevel.E_DEBUG, "Exception beim Geoscan-Einlesen");
                IRCeX.Log.WriteException(e);
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
    class ScanGeb {
        public String name;
        public uint anz;
    }
	class GebScan:IWScan {
		//protected List<ScanGeb> gebs;

		public GebScan(uint iwid, string iwhash) : base(iwid, iwhash) { }

		public override bool ToDB(MySqlConnection con, String DBPrefix, TechTreeCache tc) {
            long scanID = base.ToDB(con, DBPrefix, "geb", tc);
			if (scanID == 0)
				return false;
            MySqlCommand gebInsert = new MySqlCommand("INSERT INTO " + DBPrefix + @"scans_gebs (scanid, gebid, anzahl) VALUES (?scanid, ?gebid, ?anz)", con);
            gebInsert.Parameters.Add("?scanid", MySqlDbType.UInt32).Value = scanID;
			gebInsert.Parameters.Add("?gebid", MySqlDbType.UInt32);
			gebInsert.Parameters.Add("?anz", MySqlDbType.UInt16);
			gebInsert.Prepare();
            foreach (ScanGeb geb in Gebs) {
                gebInsert.Parameters["?gebid"].Value = tc.ID(geb.name, "geb", con, DBPrefix);
				gebInsert.Parameters["?anz"].Value = geb.anz;
				gebInsert.ExecuteNonQuery();
			}
			return true;
		}
		protected override uint getScore(TechTreeCache tc, MySqlConnection con, string DBPrefix) {
			return (uint)Gebs.Aggregate((float)0, (acc, geb) => acc + tc.Kosten(geb.name, con, DBPrefix).RaidScore * geb.anz);
		}

        public IEnumerable<ScanGeb> Gebs {
            get {
                foreach (XmlNode n in xml.SelectNodes("scann/gebaeude/gebaeude")) {
                    yield return new ScanGeb() { name = n["name"].InnerText, anz = uint.Parse(n["anzahl"].InnerText) };
                }
            }
        }
    }

    class ScanSchiff {
        public String name;
        public uint anz;
    }
    class ScanFlotte {
        public string ownerName;
        public String typ;
        public IEnumerable<ScanSchiff> schiffe;
    }
    class SchiffScan : IWScan {
		public SchiffScan(uint iwid, string iwhash):base(iwid, iwhash) {}
        public override bool ToDB(MySqlConnection con, String DBPrefix, TechTreeCache tc) {
            long scanid = (long)base.ToDB(con, DBPrefix, "schiff", tc);
            if (scanid == 0)
                return false;
            MySqlCommand flInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "scans_flotten (scanid, owner, typ) VALUES (?scanid, ?owner, ?typ)", con);
            MySqlParameter fl_scanid = flInsert.Parameters.Add("?scanid", MySqlDbType.String);
            MySqlParameter fl_owner = flInsert.Parameters.Add("?owner", MySqlDbType.String);
            MySqlParameter fl_typ = flInsert.Parameters.Add("?typ", MySqlDbType.String);
            flInsert.Prepare();
            MySqlCommand schiffsInsert = new MySqlCommand("INSERT INTO " + DBPrefix + "scans_flotten_schiffe (flid, schid, anz) VALUES (?flid, ?schid, ?anz)", con);
            MySqlParameter sch_flid = schiffsInsert.Parameters.Add("?flid", MySqlDbType.UInt32);
            MySqlParameter sch_schid = schiffsInsert.Parameters.Add("?schid", MySqlDbType.UInt32);
            MySqlParameter sch_anz = schiffsInsert.Parameters.Add("?anz", MySqlDbType.UInt32);
            schiffsInsert.Prepare();
            foreach (ScanFlotte fl in Flotten) {
                fl_scanid.Value = scanid;
                fl_owner.Value = fl.ownerName;
                fl_typ.Value = fl.typ;
                flInsert.ExecuteNonQuery();
                sch_flid.Value = flInsert.LastInsertedId;
                foreach (ScanSchiff s in fl.schiffe) {
                    schiffsInsert.Parameters["?schid"].Value = tc.ID(s.name, "sch", con, DBPrefix);
                    schiffsInsert.Parameters["?anz"].Value = s.anz;
                    schiffsInsert.ExecuteNonQuery();
                }
            }
            return true;
        }
		protected override uint getScore(TechTreeCache tkc, MySqlConnection con, string DBPrefix) {
            return (uint)Flotten.Aggregate(new ResourceSet(), (rs, f) => f.schiffe.Aggregate(rs, (r, s) => r + tkc.Kosten(s.name, con, DBPrefix) * s.anz)).RaidScore;
		}
        public IEnumerable<ScanFlotte> Flotten { get {
            return xml.SelectNodes("scann/pla_def/user").OfType<XmlNode>().Select(n => new { n = n, typ = "planetar" }).Union(xml.SelectNodes("scann/flotten_def/user").OfType<XmlNode>().Select(n => new { n = n, typ = "stationiert" })).Select(v => new ScanFlotte() {
                ownerName = v.n["name"].InnerText,
                typ=v.typ,
                schiffe = v.n.SelectNodes("schiffe/schifftyp").OfType<XmlNode>().Union(v.n.SelectNodes("defence/defencetyp").OfType<XmlNode>()).Select(s => new ScanSchiff() { name = s["name"].InnerText, anz = uint.Parse(s["anzahl"].InnerText) })
            });
        } }
    }

    abstract class IWScan {

        public static IWScan LoadXml(MySqlConnection con, String DBPrefix, WarFilter filter, uint iwid, string iwhash) {
            String url = String.Format("http://www.icewars.de/portal/kb/de/sb.php?id={0}&md_hash={1}&typ=xml", iwid, iwhash);
            XmlNode xml = IWCache.Query(url, con, DBPrefix);
            switch (xml.SelectSingleNode("scann/scann_typ/id").InnerText) {
                case "2": { //Sondierung (Gebäude/Ress)
                    GebScan s = new GebScan(iwid, iwhash);
                    s.LoadXml(xml, filter);
                    return s;
                    }
                case "3": { //Sondierung (Schiffe/Def/Ress)
                    SchiffScan s = new SchiffScan(iwid, iwhash);
                    s.LoadXml(xml, filter);
                    return s;
                    }
            }
            throw new InvalidOperationException("Unbekannter Scan-Typ!");
        }

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
		protected uint warID;

        protected XmlNode xml;

		protected IWScan(uint iwid, string iwhash) {
			this.iwid = iwid;
			this.hash = iwhash;
		}

		public void LoadXml(XmlNode xml, WarFilter warFilter) {
            this.xml = xml;
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
			WarFilter.War war = warFilter.getWar(owner_ally, timestamp);
			warID = war != null ? war.id : 0;
        }

        protected long ToDB(MySqlConnection con, String DBPrefix, String type, TechTreeCache tkc) {
            MySqlCommand scanInsert = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + @"scans (
				iwid, iwhash, time, gala, sys, pla, typ, planityp, objekttyp, ownername, ownerally, fe, st, vv, ch, ei, wa, en, warid, ressScore, score
			) VALUES (
				?iwid, ?iwhash, ?time, ?gala, ?sys, ?pla, ?typ, ?planityp, ?objekttyp, ?ownername, ?ownerally, ?fe, ?st, ?vv, ?ch, ?ei, ?wa, ?en, ?warid, ?ressScore, ?score
			)", con);
            scanInsert.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
            scanInsert.Parameters.Add("?iwhash", MySqlDbType.String).Value = hash;
            scanInsert.Parameters.Add("?time", MySqlDbType.UInt32).Value = timestamp;
            scanInsert.Parameters.Add("?gala", MySqlDbType.UInt32).Value = gal;
            scanInsert.Parameters.Add("?sys", MySqlDbType.UInt32).Value = sol;
            scanInsert.Parameters.Add("?pla", MySqlDbType.UInt32).Value = pla;
            scanInsert.Parameters.Add("?typ", MySqlDbType.String).Value = type;
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
			scanInsert.Parameters.Add("?warid", MySqlDbType.UInt32).Value = warID;
			scanInsert.Parameters.Add("?ressScore", MySqlDbType.UInt32).Value = ress.RaidScore;
			scanInsert.Parameters.Add("?score", MySqlDbType.UInt32).Value = getScore(tkc, con, DBPrefix);
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
			} else if(r.IsDBNull(0) || r.GetUInt32(0) < timestamp) {
				r.Close();
				MySqlCommand lastestScanUpdate = new MySqlCommand("UPDATE " + DBPrefix + "lastest_scans SET scanid=?scanid WHERE planid=?planid AND typ=?type", con);
				lastestScanUpdate.Parameters.Add("?planid", MySqlDbType.UInt32).Value = planid;
				lastestScanUpdate.Parameters.Add("?type", MySqlDbType.String).Value = type;
				lastestScanUpdate.Parameters.Add("?scanid", MySqlDbType.UInt32).Value = scanID;
				lastestScanUpdate.ExecuteNonQuery();
			} else {
				r.Close();
			}

            return scanID;
        }
        protected abstract uint getScore(TechTreeCache tkc, MySqlConnection con, String DBPrefix);
        public abstract bool ToDB(MySqlConnection con, String DBPrefix, TechTreeCache tkc);

        public Spieler Owner { get { return new Spieler(owner_name, owner_ally); } }
        public DateTime Time { get { return IWDBUtils.fromUnixTimestamp(timestamp); } }
        public Coords Coords { get { return new Coords() { gal = (int)gal, sys = (int)sol, pla = (int)pla }; } }
        public string Planityp { get { return this.pla_typ; } }
        public string Objettyp { get { return this.obj_typ; } }
	}
}


