using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Xml;
using MySql.Data.MySqlClient;
using IRCeX;
using System.Collections;
using System.Threading;
using Utils;
using Flow;

namespace IWDB.Parser {
	class KBParser:ReportParser {
		WarFilter warFilter;
		TechTreeCache techKostenCache;
		public KBParser(NewscanHandler newscanHandler, WarFilter warFilter, TechTreeCache tkc)
			: base(newscanHandler) {
				AddPattern(@"http://www\.icewars\.de/portal/kb/de/kb\.php\?id=(\d+)&md_hash=([a-z0-9A-Z]{32})");
				this.warFilter = warFilter;
				this.techKostenCache = tkc;
		}
		public override void Matched(System.Text.RegularExpressions.MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach(Match match in matches) {
				Kb kb = new Kb(uint.Parse(match.Groups[1].Value), match.Groups[2].Value, con, DBPrefix);

				WarFilter.War war = null;
				foreach(String tag in kb.AllyTags) {
					war = warFilter.getWar(tag, kb.TimeStamp);
					if(war!= null)
						break;
				}
				if(war == null) {
					if(kb.SaveAsRaid(con, DBPrefix)) {
						resp.Respond("RaidKB eingelesen!");
					} else {
						resp.Respond("RaidKB übersprungen!");
					}
				} else {
					if(kb.SaveAsWarKb(war.id, con, DBPrefix, techKostenCache)) {
						resp.Respond("KriegsKB eingelesen!");
					} else {
						resp.Respond("KriegsKB übersprungen!");
					}
				}
			}
		}
	}

	enum KbSaveMode {
		Raid,
		War,
		None
	}

    class Fleet {
        public String Name;
        public String Ally;
        public String StartCoords;
        public IEnumerable<KbSchiff> Ships;
    }

    class Spieler {
        public readonly String Name;
        public readonly String Ally;
        public Spieler(String Name, String Ally) {
            this.Name = Name;
            this.Ally = Ally;
        }
    }

    class BombGeb {
        public readonly String Name;
        public readonly uint GebID;
        public readonly uint Anzahl;
        public BombGeb(String name, uint gebid, uint anz) {
            this.Name = name;
            this.GebID = gebid;
            this.Anzahl = anz;
        }
    }

    class KbSchiff {
        public String Name;
        public uint SchiffID;
        public uint Anzahl_Start;
        public uint Anzahl_Ende;
        public uint Anzahl_Verlust;
        public KbSchiff(String name, uint gebid, uint start, uint ende, uint verlust) {
            this.Name = name;
            this.SchiffID = gebid;
            this.Anzahl_Start = start;
            this.Anzahl_Ende = ende;
            this.Anzahl_Verlust = verlust;
        }
    }

	class Kb {
		ResourceSet pluenderung = new ResourceSet();
		ResourceSet attresslost = new ResourceSet();
		ResourceSet defresslost = new ResourceSet();
		uint iwid;
		String hash;
		KbSaveMode saveMode;

		XmlNode xml;

		/// <summary>
		/// Läd einen neuen (noch nicht gespeicherten) KB aus dem externen XML
		/// </summary>
		public Kb(uint iwid, String hash, MySqlConnection con, String DBPrefix) {
			this.iwid = iwid;
			this.hash = hash;
			this.saveMode = KbSaveMode.None;
			ReadKbFromXml(con, DBPrefix);
		}
		/// <summary>
		/// Läd einen schon gespeicherten KB aus dem XML unter Angabe des bisherigen Speicherortes
		/// </summary>
		/// <param name="saveMode">der bisherige Speicherort des KBs</param>
		public Kb(uint iwid, String hash, KbSaveMode saveMode, MySqlConnection con, String DBPrefix) {
			this.iwid = iwid;
			this.hash = hash;
			this.saveMode = saveMode;
		}

		internal void ReadKbFromXml(MySqlConnection con, String DBPrefix) {
			String url = String.Format("http://www.icewars.de/portal/kb/de/kb.php?id={0}&md_hash={1}&typ=xml", iwid, hash);
			xml = IWCache.Query(url, con, DBPrefix);
			xml = xml["kampf"];

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
		}

		public bool SaveAsRaid(MySqlConnection con, String DBPrefix) {

			if(saveMode != KbSaveMode.None)
				throw new InvalidOperationException("Versuche KB als Raid zu speichern obwohl er schon gespeichert ist!");

			pluenderung -= attresslost;
			MySql.Data.MySqlClient.MySqlCommand cmd = new MySql.Data.MySqlClient.MySqlCommand(@"INSERT IGNORE INTO " + DBPrefix + @"raidberichte (iwid, hash, time, angreifer, angrAlly, verteidiger, verteidigerAlly, score, rFe, rSt, rCh, rVv, rEi, rWa, rEn, zFe, zSt, zCh, zVv, zEi, zWa, zEn) VALUES (?iwid, ?hash, ?time, ?angreifer, ?angrAlly, ?verteidiger, ?verteidigerAlly, ?score, ?rFe, ?rSt, ?rCh, ?rVv, ?rEi, ?rWa, ?rEn, ?zFe, ?zSt, ?zCh, ?zVv, ?zEi, ?zWa, ?zEn)", con);
			cmd.Parameters.Add("?iwid", MySql.Data.MySqlClient.MySqlDbType.UInt32).Value = iwid;
			cmd.Parameters.Add("?hash", MySql.Data.MySqlClient.MySqlDbType.String).Value = hash;
			cmd.Parameters.Add("?time", MySql.Data.MySqlClient.MySqlDbType.UInt32).Value = TimeStamp;
			cmd.Parameters.Add("?angreifer", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("flotten_att/user/name").Attributes["value"].InnerText;
			cmd.Parameters.Add("?angrAlly", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("flotten_att/user/allianz_tag").Attributes["value"].InnerText;
			cmd.Parameters.Add("?verteidiger", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("pla_def/user/name").Attributes["value"].InnerText;
			cmd.Parameters.Add("?verteidigerAlly", MySql.Data.MySqlClient.MySqlDbType.String).Value = xml.SelectSingleNode("pla_def/user/allianz_tag").Attributes["value"].InnerText;
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
				return false; //war schon eingetragen => übersprungen
			} else {
				saveMode = KbSaveMode.Raid;
				return true;
			}
		}

		public bool SaveAsWarKb(uint warID, MySqlConnection con, String DBPrefix, TechTreeCache tkc) {
			if(saveMode != KbSaveMode.None)
				throw new InvalidOperationException("Versuche KB als KriegsKB zu speichern obwohl er schon gespeichert ist!");

			MySqlCommand cmd = new MySqlCommand(@"INSERT IGNORE INTO " + DBPrefix + "war_kbs (iwid,hash,timestamp,att,attally,def,defally,attvalue,attloss,defvalue,defloss,raidvalue,bombvalue, attwin, start, dst, warid) VALUES (?iwid,?hash,?timestamp,?att,?attally,?def,?defally,?attvalue,?attloss,?defvalue,?defloss,?raidvalue,?bombvalue,?attwin,?start,?dst,?warid)", con);
			cmd.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
			cmd.Parameters.Add("?hash", MySqlDbType.String).Value = hash;
			cmd.Parameters.Add("?timestamp", MySqlDbType.UInt32).Value = TimeStamp;

            IEnumerable<Spieler> att = Attackers;
            IEnumerable<Spieler> def = Defenders;

			cmd.Parameters.Add("?att", MySqlDbType.String).Value = att.Select(p => p.Name).Distinct().Aggregate((x, y) => x+ ", " + y);
			cmd.Parameters.Add("?attally", MySqlDbType.String).Value = att.Select(p => p.Ally).Distinct().Aggregate((x, y) => x + ", " + y);
			cmd.Parameters.Add("?def", MySqlDbType.String).Value = def.Select(p => p.Name).Distinct().Aggregate((x, y) => x + ", " + y);
			cmd.Parameters.Add("?defally", MySqlDbType.String).Value = def.Select(p => p.Ally).Distinct().Aggregate((x, y) => x + ", " + y);

            cmd.Parameters.Add("?attvalue", MySqlDbType.UInt32).Value = (uint)(AttShips.Aggregate(new ResourceSet(), (rs, ship) => rs + ship.Anzahl_Start * tkc.Kosten(ship.Name, con, DBPrefix)).RaidScore);
			cmd.Parameters.Add("?attloss", MySqlDbType.UInt32).Value = attresslost.RaidScore;
            cmd.Parameters.Add("?defvalue", MySqlDbType.UInt32).Value = (uint)(DefShips.Aggregate(new ResourceSet(), (rs, ship) => rs + ship.Anzahl_Start * tkc.Kosten(ship.Name, con, DBPrefix)).RaidScore);
			cmd.Parameters.Add("?defloss", MySqlDbType.UInt32).Value = defresslost.RaidScore;
			cmd.Parameters.Add("?raidvalue", MySqlDbType.UInt32).Value = pluenderung.RaidScore;
			cmd.Parameters.Add("?bombvalue", MySqlDbType.UInt32).Value = Bombed.Aggregate((uint)0, (n, tp) => n + tp.Anzahl);

			cmd.Parameters.Add("?attwin", MySqlDbType.UInt32).Value = "1" == xml.SelectSingleNode("resultat/id").Attributes["value"].InnerText;
			cmd.Parameters.Add("?start", MySqlDbType.String).Value = StartCoords.Aggregate(new StringBuilder(), (sb, coords) => sb.AppendLine(coords), sb => sb.Length > 0 ? sb.ToString(0, sb.Length-Environment.NewLine.Length) : "");
			cmd.Parameters.Add("?dst", MySqlDbType.String).Value = DstCoords;
			cmd.Parameters.Add("?warid", MySqlDbType.UInt32).Value = warID;

			cmd.Prepare();
			if(cmd.ExecuteNonQuery() == 0) {
				return false; //war schon eingetragen => übersprungen
			} else {
				saveMode = KbSaveMode.War;
				return true;
			}
		}

		public bool RemoveFromDB(MySqlConnection con, String DBPrefix) {
			int removed = 0;
			switch(saveMode) {
				case KbSaveMode.None:
					throw new InvalidOperationException("Versuche KB zu löschen der nicht gespeichert ist!");
				case KbSaveMode.Raid:
					MySqlCommand delRaid = new MySqlCommand(@"DELETE FROM "+DBPrefix+"raidberichte WHERE iwid=?iwid AND hash=?hash", con);
					delRaid.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
					delRaid.Parameters.Add("?hash", MySqlDbType.String).Value = hash;
					delRaid.Prepare();
					removed = delRaid.ExecuteNonQuery();
					break;
				case KbSaveMode.War:
					MySqlCommand delWarKb = new MySqlCommand(@"DELETE FROM "+DBPrefix+"war_kbs WHERE iwid=?iwid AND hash=?hash", con);
					delWarKb.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
					delWarKb.Parameters.Add("?hash", MySqlDbType.String).Value = hash;
					delWarKb.Prepare();
					removed = delWarKb.ExecuteNonQuery();
					break;
			}
			saveMode = KbSaveMode.None;
			return removed != 0;
		}

		public uint TimeStamp { get { return uint.Parse(xml.SelectSingleNode("timestamp").Attributes["value"].InnerText); } }
        public IEnumerable<Spieler> Attackers {
			get {
				foreach(XmlNode n in xml.SelectNodes("flotten_att")) {
                    yield return new Spieler(n["user"]["name"].Attributes["value"].InnerText, n["user"]["allianz_tag"].Attributes["value"].InnerText);
				}
			}
		}
        public Spieler Owner {
            get { return new Spieler(xml["pla_def"]["user"]["name"].Attributes["value"].InnerText, xml["pla_def"]["user"]["allianz_tag"].Attributes["value"].InnerText); }
        }
        public IEnumerable<Spieler> Defenders {
			get {
                yield return new Spieler(xml["pla_def"]["user"]["name"].Attributes["value"].InnerText, xml["pla_def"]["user"]["allianz_tag"].Attributes["value"].InnerText);
				foreach(XmlNode n in xml.SelectNodes("flotten_def")) {
                    yield return new Spieler(n["user"]["name"].Attributes["value"].InnerText, n["user"]["allianz_tag"].Attributes["value"].InnerText);
				}
			}
		}
        public IEnumerable<KbSchiff> AttShips {
			get {
				foreach(XmlNode node in xml.SelectNodes("flotten_att")) {
					foreach(XmlNode n in node.SelectNodes("user/schiffe/schifftyp")) {
                        yield return new KbSchiff(
							n["name"].Attributes["value"].InnerText, 
							uint.Parse(n["id"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						);
					}
				}
			}
		}
        public IEnumerable<KbSchiff> DefShips {
            get {
                foreach (XmlNode node in xml.SelectNodes("flotten_def").OfType<XmlNode>().Union(xml.SelectNodes("pla_def").OfType<XmlNode>())) {
                    foreach (XmlNode n in node.SelectNodes("user/schiffe/schifftyp")) {
                        yield return new KbSchiff(
                            n["name"].Attributes["value"].InnerText,
                            uint.Parse(n["id"].Attributes["value"].InnerText),
                            uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
                            uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
                            uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
                        );
                    }
                }
            }
        }
        public IEnumerable<KbSchiff> DefUnits {
			get {
                foreach (XmlNode node in xml.SelectNodes("flotten_def").OfType<XmlNode>().Union(xml.SelectNodes("pla_def").OfType<XmlNode>())) {
					foreach(XmlNode n in node.SelectNodes("user/schiffe/schifftyp")) {
						yield return new KbSchiff(
							n["name"].Attributes["value"].InnerText,
							uint.Parse(n["id"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						);
					}
					foreach(XmlNode n in node.SelectNodes("user/defence/defencetyp")) {
						yield return new KbSchiff(
							n["name"].Attributes["value"].InnerText,
							uint.Parse(1000+n["id"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						);
					}
				}
			}
		}
        public IEnumerable<Fleet> AttFleets {
            get {
                foreach (XmlNode node in xml.SelectNodes("flotten_att/user")) {
                    yield return new Fleet() {
                        Name = node["name"].Attributes["value"].InnerText,
                        Ally = node["allianz_tag"].Attributes["value"].InnerText,
                        StartCoords = node["startplanet"]["koordinaten"]["string"].Attributes["value"].InnerText,
                        Ships = node.SelectNodes("schiffe/schifftyp").OfType<XmlNode>().Select(n =>
                            new KbSchiff(
                                n["name"].Attributes["value"].InnerText,
                                uint.Parse(n["id"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
                            ))
                    };
                }
            }
        }
        public IEnumerable<Fleet> DefFleets {
            get {
                String coords = xml.SelectSingleNode("plani_data/koordinaten/string").Attributes["value"].InnerText;
                foreach (XmlNode node in xml.SelectNodes("flotten_def/user").OfType<XmlNode>().Union(xml.SelectNodes("pla_def/user").OfType<XmlNode>())) {
                    yield return new Fleet() {
                        Name = node["name"].Attributes["value"].InnerText,
                        Ally = node["allianz_tag"].Attributes["value"].InnerText,
                        StartCoords = coords,
                        Ships = node.SelectNodes("schiffe/schifftyp").OfType<XmlNode>().Select(n =>
                            new KbSchiff(
                                n["name"].Attributes["value"].InnerText,
                                uint.Parse(n["id"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
                                uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
                            ))
                    };
                }
            }
        }
		public IEnumerable<String> AllyTags {
			get {
				foreach(XmlNode n in xml.SelectNodes("flotten_att")) {
					yield return n["user"]["allianz_tag"].Attributes["value"].InnerText;
				}
				foreach(XmlNode n in xml.SelectNodes("flotten_def")) {
					yield return n["user"]["allianz_tag"].Attributes["value"].InnerText;
				}
				yield return xml["pla_def"]["user"]["allianz_tag"].Attributes["value"].InnerText;
			}
		}
        public IEnumerable<BombGeb> Bombed {
			get {
				foreach(XmlNode n in xml.SelectNodes("bomben/geb_zerstoert/geb")) {
                    yield return new BombGeb(n["name"].Attributes["value"].InnerText, uint.Parse(n["id"].Attributes["value"].InnerText), uint.Parse(n["anzahl"].Attributes["value"].InnerText)); 
				}
			}
		}
		public IEnumerable<String> StartCoords {
			get {
				foreach(XmlNode n in xml.SelectNodes("flotten_att/user/startplanet/koordinaten/string")) {
					yield return n.Attributes["value"].InnerText;
				}
			}
		}
		public String DstCoords { get { return xml.SelectSingleNode("plani_data/koordinaten/string").Attributes["value"].InnerText; } }
		public KbSaveMode SaveMode { get { return saveMode; } }
        public bool AttWin { get { return xml.SelectSingleNode("resultat/id").Attributes["value"].InnerText == "1"; } }
        public bool Bomb { get { return xml.SelectSingleNode("bomben") != null; } }
        public bool Plopp { get { return xml.SelectSingleNode("bomben/basis_zerstoert[@value='1']") != null; } }
	}

	class TechTreeCache {
		protected Dictionary<String, ResourceSet> kostenCache = new Dictionary<string, ResourceSet>();
        protected DefaultDict<String, Dictionary<String, uint>> idCache = new DefaultDict<string, Dictionary<string, uint>>(() => new Dictionary<string, uint>());
		public void Clear() {
			lock(kostenCache) {
				kostenCache.Clear();
                idCache.Clear();
			}
		}
		public ResourceSet Kosten(String name, MySqlConnection con, String DBPrefix) {
			lock(kostenCache) {
				ResourceSet ret = null;
				if(kostenCache.TryGetValue(name, out ret))
					return ret;
				MySqlCommand cmd = new MySqlCommand(@"SELECT Dauer, bauE, bauS, bauC, bauV, bauEis, bauW, bauEn, bauCr, bauBev FROM " + DBPrefix + @"techtree_items AS techtree_items INNER JOIN " + DBPrefix + "techtree_stufen AS techtree_stufen ON techtree_items.ID=techtree_stufen.ItemID WHERE techtree_items.Name=?name AND techtree_items.type <> 'for'", con);
				cmd.Parameters.Add("?name", MySqlDbType.String).Value = name;
				cmd.Prepare();
				MySqlDataReader r = cmd.ExecuteReader();
				try {
					ret = new ResourceSet();
					if(!r.Read()) {
						kostenCache.Add(name, ret);
						return ret;
					}
					ret.Zeit = TimeSpan.FromSeconds(r.GetUInt32(0));
					ret.Eisen = r.GetUInt32(1);
					ret.Stahl = r.GetUInt32(2);
					ret.Chemie = r.GetUInt32(3);
					ret.VV4A = r.GetUInt32(4);
					ret.Eis = r.GetUInt32(5);
					ret.Wasser = r.GetUInt32(6);
					ret.Energie = r.GetUInt32(7);
					ret.Credits = r.GetUInt32(8);
					ret.Bev = r.GetUInt32(9);
					kostenCache.Add(name, ret);
				} finally {
					r.Close();
				}
				return ret;
			}
		}
        public uint ID(String name, String type, MySqlConnection con, String DBPrefix) {
            lock (kostenCache) {
                Dictionary<string, uint> cache = idCache[type];
                uint ret = 0;
                if (cache.TryGetValue(name, out ret))
                    return ret;
                MySqlCommand cmd = new MySqlCommand("SELECT ID FROM " + DBPrefix + "techtree_items WHERE name=?name AND type=?type", con);
                cmd.Parameters.Add("?name", MySqlDbType.String).Value = name;
                cmd.Parameters.Add("?type", MySqlDbType.String).Value = type;
                cmd.Prepare();
                object obj = cmd.ExecuteScalar();
                if (obj != null) {
                    ret = (uint)obj;
                    cache.Add(name, ret);
                    return ret;
                }
                MySqlCommand insert = new MySqlCommand("INSERT INTO " + DBPrefix + "techtree_items (name, type) VALUES (?name, ?type)", con);
                insert.Parameters.Add("?name", MySqlDbType.String).Value = name;
                insert.Parameters.Add("?type", MySqlDbType.String).Value = type;
                insert.Prepare();
                insert.ExecuteNonQuery();
                ret = (uint)insert.LastInsertedId;
                cache.Add(name, ret);
                return ret;
            }
        }
	}

	class WarFilter : RequestHandler {
		public class War {
			public uint id;
			public String name;
			public String allytag;
			public uint begin;
			public uint end;

			public War(uint id, String name, String tag, uint begin, uint end) {
				this.id = id;
				this.name = name;
				this.allytag = tag;
				this.begin = begin;
				this.end = end;
			}

		}

		List<War> wars = new List<War>();
		String DBPrefix;
		MySqlConnection con;
        String connectionString;
        object warRefreshLock = new object();
		public TechTreeCache TechKostenCache;
		public WarFilter(String DBPrefix, MySqlConnection con, TechTreeCache tkc, String connectionString) {
			this.DBPrefix = DBPrefix;
			this.con = con;
			this.TechKostenCache = tkc;
            this.connectionString = connectionString;
			Reload(con);
		}

		protected void Reload(MySqlConnection con) {
			lock(wars) {
				wars.Clear();
				TechKostenCache.Clear();
				MySqlCommand cmd = new MySqlCommand("SELECT id, name, allytag, begin, end FROM " + DBPrefix + "wars", con);
				MySqlDataReader r = cmd.ExecuteReader();
				try {
					while(r.Read()) {
						wars.Add(new War(r.GetUInt32(0), r.GetString(1), r.GetString(2), r.GetUInt32(3), r.GetUInt32(4)));
					}
				} finally {
					r.Close();
				}
				Log.WriteLine(LogLevel.E_DEBUG, "Reloaded Wars:");
				foreach(War w in wars) {
					Log.WriteLine(LogLevel.E_DEBUG, String.Format("War: {0} {1} {2} {3} {4}", w.id, w.name, w.allytag, w.begin, w.end));
				}
			}
		}

		public War getWar(String allytag, uint timestamp) {
			lock(wars) {
				foreach(War w in wars) {
					if(w.allytag == allytag && timestamp > w.begin && timestamp < w.end)
						return w;
				}
				return null;
			}
		}

		public bool InWar { get { uint now = IWDBUtils.toUnixTimestamp(DateTime.Now); return wars.Any(w => w.begin <= now && now <= w.end); } }

        protected void WarRefresh() {
            Monitor.Enter(warRefreshLock);
            try {
                Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh");
                MySqlConnection con = new MySqlConnection(connectionString);
                //IRCeX.Log.WriteLine("MySqlOpen: WarRefresh");
                con.Open();
                Reload(con);
                lock (wars) {
                    Log.WriteLine(LogLevel.E_NOTICE, DateTime.Now.ToString() + " " + wars.Count + " Kriege neu geladen & Techtree-Cache geleert!");
                }
                uint oldWar = 0, oldRaid = 0;
                List<Kb> kbs = new List<Kb>();
                MySqlCommand cmd_raid = new MySqlCommand(@"SELECT iwid, hash FROM " + DBPrefix + @"raidberichte", con);
                MySqlDataReader r = cmd_raid.ExecuteReader();
                try {
                    while (r.Read()) {
                        oldRaid++;
                        kbs.Add(new Kb(r.GetUInt32(0), r.GetString(1), KbSaveMode.Raid, con, DBPrefix));
                    }
                } finally {
                    r.Close();
                }

                MySqlCommand cmd_war = new MySqlCommand(@"SELECT iwid, hash FROM " + DBPrefix + @"war_kbs", con);
                r = cmd_war.ExecuteReader();
                try {
                    while (r.Read()) {
                        oldWar++;
                        kbs.Add(new Kb(r.GetUInt32(0), r.GetString(1), KbSaveMode.War, con, DBPrefix));
                    }
                } finally {
                    r.Close();
                }

                uint newWar = 0, newRaid = 0;
                foreach (Kb kb in kbs) {
                    if (((newWar + newRaid) % 500) == 0) {
                        Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh: .");
                    }
                    kb.ReadKbFromXml(con, DBPrefix);
                    War war = null;
                    foreach (String tag in kb.AllyTags) {
                        war = getWar(tag, kb.TimeStamp);
                        if (war != null)
                            break;
                    }
                    kb.RemoveFromDB(con, DBPrefix);
                    if (war != null) {
                        newWar++;
                        kb.SaveAsWarKb(war.id, con, DBPrefix, TechKostenCache);
                    } else {
                        newRaid++;
                        kb.SaveAsRaid(con, DBPrefix);
                    }
                }
                Log.WriteLine(LogLevel.E_NOTICE, DateTime.Now.ToString() + " " + newWar + " Kriegs- (zuvor " + oldWar + ") und " + newRaid + " Raid-KBs (" + oldRaid + " zuvor) neu berechnet!");

                kbs.Clear();

                List<Tuple<uint, uint, string>> scans = new List<Tuple<uint, uint, string>>();
                MySqlCommand cmd_scans = new MySqlCommand(@"SELECT id, iwid, iwhash FROM " + DBPrefix + "scans", con);
                r = cmd_scans.ExecuteReader();
                try {
                    while (r.Read()) {
                        scans.Add(new Tuple<uint, uint, string>(r.GetUInt32(0), r.GetUInt32(1), r.GetString(2)));
                    }
                } finally {
                    r.Close();
                }
                StringBuilder flotten = new StringBuilder();
                MySqlCommand cmd_fl = new MySqlCommand("SELECT id FROM " + DBPrefix + "scans_flotten WHERE scanid=?scanid", con);
                cmd_fl.Parameters.Add("?scanid", MySqlDbType.UInt32);
                cmd_fl.Prepare();
                MySqlCommand cmd_flDel = new MySqlCommand("DELETE FROM " + DBPrefix + "scans_flotten WHERE scanid=?scanid", con);
                cmd_flDel.Parameters.Add("?scanid", MySqlDbType.UInt32);
                cmd_flDel.Prepare();
                MySqlCommand cmd_scanDel = new MySqlCommand("DELETE FROM " + DBPrefix + "scans WHERE id=?scanid", con);
                cmd_scanDel.Parameters.Add("?scanid", MySqlDbType.UInt32);
                cmd_scanDel.Prepare();
                uint gebscan_cnt = 0, schiffscan_cnt = 0;
                foreach (Tuple<uint, uint, string> tpl in scans) {
                    if (((gebscan_cnt + schiffscan_cnt) % 500) == 0) {
                        Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh: .");
                    }
                    flotten.Clear();
                    cmd_fl.Parameters["?scanid"].Value = tpl.Item1;
                    cmd_flDel.Parameters["?scanid"].Value = tpl.Item1;
                    cmd_scanDel.Parameters["?scanid"].Value = tpl.Item1;
                    r = cmd_fl.ExecuteReader();
                    try {
                        while (r.Read()) {
                            flotten.Append(r.GetUInt32(0));
                            flotten.Append(", ");
                        }
                    } finally {
                        r.Close();
                    }
                    if (flotten.Length > 0) {
                        flotten.Length -= 2;
                        MySqlCommand cmd_shipDel = new MySqlCommand("DELETE FROM " + DBPrefix + "scans_flotten_schiffe WHERE flid IN (" + flotten.ToString() + ")", con);
                        cmd_shipDel.ExecuteNonQuery();
                    }
                    cmd_flDel.ExecuteNonQuery();
                    cmd_scanDel.ExecuteNonQuery();
                    IWScan scan = IWScan.LoadXml(con, DBPrefix, this, tpl.Item2, tpl.Item3);
                    scan.ToDB(con, DBPrefix, TechKostenCache);
                    if (scan is SchiffScan)
                        schiffscan_cnt++;
                    else if (scan is GebScan)
                        gebscan_cnt++;
                }
                Log.WriteLine(LogLevel.E_NOTICE, DateTime.Now.ToString() + " " + gebscan_cnt + " Gebscans und " + schiffscan_cnt + " Schiffscans neu eingelesen!");
            } finally {
                Monitor.Exit(warRefreshLock);
            }
        }

		public void HandleRequest(ParserRequestMessage msg) {
			try {
                msg.AnswerLine("WarRefresh gestartet");
                new Thread(WarRefresh).Start();
			} finally {
				try {
					msg.Handled();
				} finally {
					//IRCeX.Log.WriteLine("MySqlClose: WarRefresh");
					con.Close();
				}
			}
		}

		public string Name {
			get { return "WarFilter"; }
		}
	}

    class FremderScanParser : ReportParser {
        public FremderScanParser(NewscanHandler newscanHandler)
            : base(newscanHandler) {
                this.AddPattern(@"Eigener\sPlanet\swurde\ssondiert\s" + KoordinatenMatch + @"\s+Systemnachricht\s+(" + PräziseIWZeit + @")\s+Sondierung\s\((Schiffe/Def/Ress|Gebäude/Ress|Geologie)\)\s+([^\n]+)");
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
            MySqlCommand cmd = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + "feind_scans (dst, time, type, start, sender, ally ) VALUES (?dst, ?time, ?type, ?start, ?sender, ?ally)", con);
            cmd.Parameters.Add("?dst", MySqlDbType.VarChar);
            cmd.Parameters.Add("?time", MySqlDbType.UInt32);
            cmd.Parameters.Add("?type", MySqlDbType.VarChar);
            cmd.Parameters.Add("?start", MySqlDbType.VarChar);
            cmd.Parameters.Add("?sender", MySqlDbType.VarChar);
            cmd.Parameters.Add("?ally", MySqlDbType.VarChar);
            cmd.Prepare();
            List<String> innerPatterns = new List<string>() {
                @"Ok,\sder\sPlanet\s"+Koordinaten+@"\swurde\sausspioniert\.\sUnd\sauch\serfolgreich\.\sNämlich\svon\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch,
                @"Planet\s"+Koordinaten+@"\svon\sdem\sbösen\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch+@"\sausspioniert!",
                @"Planet\s"+Koordinaten+@"\swurde\sausspioniert\.\sVon\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch,
                @"MEEEEP\sMEEEP\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch+@"\shat",
                @"Der/Die/Das\s\(unzutreffendes\sbitte\sstreichen\)\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch,
                @"Planeten\s"+Koordinaten+@"\svon\s("+SpielerName+@")\s("+AllyTag+@")\sausspioniert\.\sDiese\sunerhörte\sArt\sder\sAggression\sging\svom\sPlaneten\s"+KoordinatenMatch+@"\saus",
                @"Heute\shat\ses\sder\sfiese\s("+SpielerName+@")\s("+AllyTag+@")\s"+KoordinatenMatch+@"\sgewagt", 
            };
            foreach (Match m in matches) {
                cmd.Parameters["?dst"].Value = m.Groups[1].Value;
                cmd.Parameters["?time"].Value = IWDBUtils.parsePreciseIWTime(m.Groups[2].Value);
                cmd.Parameters["?type"].Value = m.Groups[3].Value == "Schiffe/Def/Ress" ? "sch" : m.Groups[3].Value == "Geologie" ? "geo": "geb";

                bool found = false;
                foreach (String pattern in innerPatterns) {
                    Match innerMatch = Regex.Match(m.Groups[4].Value, pattern, RegexOptions.IgnorePatternWhitespace);
                    if (innerMatch.Success) {
                        cmd.Parameters["?start"].Value = innerMatch.Groups[3].Value;
                        cmd.Parameters["?sender"].Value = innerMatch.Groups[1].Value;
                        cmd.Parameters["?ally"].Value = innerMatch.Groups[2].Value;
                        if (cmd.ExecuteNonQuery() > 0)
                            resp.Respond("Feindlichen Scan eingelesen");
                        else
                            resp.Respond("Feindlichen Scan übersprungen");
                        found = true;
                        break;
                    }
                }
                if (!found)
                    resp.RespondError("Unbekannte Scanbeschreibung: " + ConfigUtils.XmlEscape(m.Groups[4].Value));
            }
        }
    }

    class WarStats : RequestHandler {
        String DBPrefix;
        String connectionString;
        WarFilter warFilter;
        TechTreeCache tkc;
        object generatorLock = new object();
        public WarStats(String DBPrefix, String connectionString, WarFilter warFilter) {
            this.DBPrefix = DBPrefix;
            this.connectionString = connectionString;
            this.warFilter = warFilter;
            this.tkc = warFilter.TechKostenCache;
        }

        class SchiffSichtung {
            public Kb Kb;
            public String Name;
            public String Ally;
            public uint Anzahl;
            public TimeSpan AnflugZeit;
            public DateTime Zeit;
            public String ZielCoords;
            public String StartCoords;
        }

        private int MeisteGleichzeitig(IEnumerable<SchiffSichtung> sichtungen, String schiff) {
            List<SchiffSichtung> alle = new List<SchiffSichtung>(sichtungen.OrderBy(s => s.Zeit.Ticks));
            MinimumFlowNetwork net = new MinimumFlowNetwork(2 * alle.Count + 2, 2 * alle.Count, 2 * alle.Count + 1);
            for (int i = 0; i < alle.Count; ++i) {
                SchiffSichtung s = alle[i];
                net.c[net.s, 2 * i] = (int)s.Anzahl;
                net.c[2 * i + 1, net.t] = (int)s.Anzahl;
                net.c[2 * i, 2 * i + 1] = (int)s.Anzahl;
                net.l[2 * i, 2 * i + 1] = (int)s.Anzahl;
                DateTime startZeit = s.Zeit - s.AnflugZeit;
                for (int j = 0; j < i; ++j) {
                    SchiffSichtung s2 = alle[j];
                    if (s2.Zeit + FlugRechner.MinZeit(s2.ZielCoords, s.StartCoords, schiff) <= startZeit) {
                        net.c[2 * j + 1, 2 * i] = (int)s2.Anzahl;
                    }
                }
            }
            return net.MinFlow();
        }

        private StringBuilder FormatTable(StringBuilder sb, DefaultDict<String, DefaultDict<String, long>> tbl, String title, String id, DefaultDict<String, DefaultDict<String, long>> backgroundInfo = null, bool showsum = true, bool showpercent = false, Func<long, string> backgroundFormatter=null) {
            Func<long, string> formatter = el => el.ToString("N0");
            if (backgroundFormatter == null)
                backgroundFormatter = formatter;
            return FormatTable(sb, tbl, backgroundInfo, title, id, showsum, showpercent, (a1, a2) => a1 + a2, (a1, a2) => (double)a1 / a2, formatter, backgroundFormatter);
        }
        private StringBuilder FormatTable(StringBuilder sb, DefaultDict<String, DefaultDict<String, double>> tbl, String title, String id, DefaultDict<String, DefaultDict<String, double>> backgroundInfo = null, bool showsum = false, bool showpercent = false, Func<double, string> backgroundFormatter = null) {
            Func<double, string> formatter = el => el.ToString("N0");
            if (backgroundFormatter == null)
                backgroundFormatter = formatter;
            return FormatTable(sb, tbl, backgroundInfo, title, id, showsum, showpercent, (a1, a2) => a1 + a2, (a1, a2) => a1 / a2, formatter, backgroundFormatter);
        }
        private StringBuilder FormatTable<T>(StringBuilder sb, DefaultDict<String, DefaultDict<String, T>> tbl, DefaultDict<String, DefaultDict<String, T>> backgroundInfo, String title, String id, bool showSum, bool showPercent, Func<T, T, T> Add, Func<T, T, double> Div, Func<T, String> formatter, Func<T, String> backgroundFormatter) {
            bool showInfo = backgroundInfo != null;
            if (backgroundInfo == null)
                backgroundInfo = new DefaultDict<string, DefaultDict<string, T>>();
            IEnumerable<string> keys = tbl.Keys.Union(backgroundInfo.Keys).Distinct();
            sb.Append("<table class=\"tablesorter\" id=\""+id+"\"><thead><tr><th>").Append(Escape.Html(title)).Append("</th>");
            keys.ForEach(k => sb.Append("<th>").Append(Escape.Html(k)).Append("</th>").Append(showPercent?"<th style=\"width:70px;\">%</th>":""));
            if(showSum)
                sb.Append("<th>Gesamt</th>");
            sb.Append("</tr></thead><tbody>");
            foreach (String ttle in tbl.Values.Aggregate(Enumerable.Empty<string>(), (acc,d) => acc.Union(d.Keys)).Distinct()) {
                sb.Append("<tr><td>").Append(Escape.Html(ttle)).Append("</td>");
                T sum = keys.Select(k => tbl[k][ttle]).Aggregate(default(T), Add);
                foreach(String k in keys) {
                    T t = tbl[k][ttle];
                    sb.Append("<td>").Append(Escape.Html(formatter(t)));
                    if (showInfo)
                        sb.Append(Escape.Html(backgroundFormatter(backgroundInfo[k][ttle])));
                    sb.Append("</td>");
                    if (showPercent) {
                        double pcnt = 100 * Div(t, sum);
                        sb.Append("<td>").Append(Escape.Html(pcnt.ToString("n0"))).Append("%</td>");
                    }
                }
                if(showSum)
                    sb.Append("<td>").Append(Escape.Html(formatter(sum))).Append("</td>");
                sb.Append("</tr>");
            }
            sb.Append("</tbody></table><script type=\"text/javascript\">$(function(){$(\"#").Append(id).AppendLine("\").tablesorter();});</script>");
            return sb;
        }
        private DefaultDict<string, DefaultDict<string, T>> Transpose<T>(DefaultDict<string, DefaultDict<string, T>> d) {
            DefaultDict<string, DefaultDict<string, T>> ret = new DefaultDict<string, DefaultDict<string, T>>(() => new DefaultDict<string, T>());
            d.ForEach(outer => outer.Value.ForEach(inner => ret[inner.Key][outer.Key] = inner.Value));
            return ret;
        }

        protected String GenerateStats(List<Kb> kbs, List<GebScan> gebScans, List<SchiffScan> schiffScans, WarFilter.War war, MySqlConnection con) {
            StringBuilder stats = new StringBuilder();
            stats.Append("Stats begonnen um ").Append(DateTime.Now.ToString()).AppendLine("<br />");
            stats.AppendLine(kbs.Count() + " Kampfberichte verarbeitet<br />");
            stats.AppendLine(gebScans.Count() + " Gebscans verarbeitet<br />");
            stats.AppendLine(schiffScans.Count() + " Schiffscans verarbeitet<br />");
            stats.AppendLine("<h3>Angriffe</h3>");
            DefaultDict<String, DefaultDict<String, long>> angriffe = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            angriffe.AddRange(kbs.SelectMany(kb => kb.Attackers.Select(att => att.Ally).Distinct().Select(att => new { ally = att, win = kb.AttWin, bomb = kb.Bomb, plopp = kb.Plopp })).GroupBy(att => att.ally).Select(ally => new Tuple<string, DefaultDict<string, long>>(ally.Key, new DefaultDict<string, long>() { { "Angriffe", ally.Count() }, { "Siege", ally.Count(kb => kb.win) }, { "Bombings", ally.Count(kb => kb.bomb) }, { "Plopps", ally.Count(kb => kb.plopp) } })));
            FormatTable(stats, angriffe, "Angriffe", "att_"+war.id, showpercent:true);

            stats.AppendLine("<h3>Verteidigungen</h3>");
            DefaultDict<String, DefaultDict<String, long>> verteidigungen = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            verteidigungen.AddRange(kbs.SelectMany(kb => kb.Defenders.Select(att => att.Ally).Distinct().Select(att => new { ally = att, win = kb.AttWin, bomb = kb.Bomb, plopp = kb.Plopp })).GroupBy(att => att.ally).Select(ally => new Tuple<string, DefaultDict<string, long>>(ally.Key, new DefaultDict<string, long>() { { "Verteidigungen", ally.Count() }, { "Siege", ally.Count(kb => !kb.win) }, { "Bombings", ally.Count(kb => kb.bomb) }, { "Plopps", ally.Count(kb => kb.plopp) } })));
            FormatTable(stats, verteidigungen, "Verteidigungen", "def_" + war.id, showpercent:true);

            stats.AppendLine("<h3>Angriffe (Spieler)</h3>");
            DefaultDict<String, DefaultDict<String, long>> angriffeSpieler = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            angriffeSpieler.AddRange(kbs.SelectMany(kb => kb.Attackers.Select(att => att.Name).Distinct().Select(att => new { spieler = att, win = kb.AttWin, bomb = kb.Bomb, plopp = kb.Plopp })).GroupBy(att => att.spieler).Select(spieler => new Tuple<string, DefaultDict<string, long>>(spieler.Key, new DefaultDict<string, long>() { { "Angriffe", spieler.Count() }, { "Siege", spieler.Count(kb => kb.win) }, { "Bombings", spieler.Count(kb => kb.bomb) }, { "Plopps", spieler.Count(kb => kb.plopp) } })));
            angriffeSpieler = Transpose(angriffeSpieler); //Transponieren zum transponieren FTW!
            FormatTable(stats, angriffeSpieler, "Spieler", "att_spieler_" + war.id, showsum:false);

            stats.AppendLine("<h3>Verteidigungen (Spieler)</h3>");
            DefaultDict<String, DefaultDict<String, long>> verteidigungenSpieler = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            verteidigungenSpieler.AddRange(kbs.SelectMany(kb => kb.Defenders.Select(att => att.Name).Distinct().Select(att => new { spieler = att, win = kb.AttWin, bomb = kb.Bomb, plopp = kb.Plopp })).GroupBy(att => att.spieler).Select(spieler => new Tuple<string, DefaultDict<string, long>>(spieler.Key, new DefaultDict<string, long>() { { "Verteidigungen", spieler.Count() }, { "Siege", spieler.Count(kb => !kb.win) }, { "Bombings", spieler.Count(kb => kb.bomb) }, { "Plopps", spieler.Count(kb => kb.plopp) } })));
            verteidigungenSpieler = Transpose(verteidigungenSpieler);
            FormatTable(stats, verteidigungenSpieler, "Spieler", "def_spieler_" + war.id, showsum: false);

            stats.AppendLine("<h3>Verlorene Schiffe</h3>");
            DefaultDict<String, DefaultDict<String, long>> schiffeVerloren = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            foreach (IGrouping<string, Fleet> grp in kbs.SelectMany(kb => kb.DefFleets.Union(kb.AttFleets)).GroupBy(fl => fl.Ally)) {
                schiffeVerloren.Add(grp.Key, new DefaultDict<string, long>().AddRange(grp.SelectMany(fl => fl.Ships).GroupBy(sh => sh.SchiffID).Select(gp => new Tuple<string, long>(gp.First().Name, gp.Sum(sch => sch.Anzahl_Verlust)))));
            }
            FormatTable(stats, schiffeVerloren, "Schiff", "schiff_" + war.id, showpercent: true);

            stats.AppendLine("<h3>Gesichtete Schiffe</h3>");
            Dictionary<string, bool> interessanteSchiffe = new Dictionary<string, bool>() {
                {"Gatling", true},{"Succubus", true},{"Kronk", true},{"Atombomber", true},{"Manta", true},{"Stormbringer", true},{"X12 (Carrier)", true},{"Zeus", true}
            };
            DefaultDict<string, DefaultDict<String, long>> schiffeGesichtet = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            foreach (IGrouping<string, SchiffSichtung> allies in kbs.SelectMany(kb => kb.AttFleets.Union(kb.DefFleets).SelectMany(fl => fl.Ships.Select(sch => new SchiffSichtung() { Kb = kb, Name = sch.Name, Zeit = IWDBUtils.fromUnixTimestamp(kb.TimeStamp), AnflugZeit = fl.StartCoords == kb.DstCoords ? TimeSpan.Zero : FlugRechner.MinZeit(fl.StartCoords, kb.DstCoords, sch.Name).Add(TimeSpan.FromMinutes(15)), Anzahl = sch.Anzahl_Ende, Ally = fl.Ally, StartCoords = fl.StartCoords, ZielCoords = kb.DstCoords }))).GroupBy(s => s.Ally)) {
                foreach(IGrouping<string, SchiffSichtung> sichtungen in allies.GroupBy(s=>s.Name)) {
                    if (interessanteSchiffe.ContainsKey(sichtungen.Key)) {
                        schiffeGesichtet[allies.Key][sichtungen.Key] = MeisteGleichzeitig(sichtungen, sichtungen.Key);
                    }
                }
            }
            FormatTable(stats, schiffeGesichtet, "Schiff", "schiffe_gesichtet_" + war.id);

            stats.AppendLine("<h3>Verlorene Gebäude</h3>");
            DefaultDict<String, DefaultDict<String, long>> gebsVerloren = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            gebsVerloren.AddRange(kbs.Where(kb => kb.Bomb).GroupBy(kb => kb.Owner.Ally).Select(ally => new Tuple<string, DefaultDict<string, long>>(ally.Key, new DefaultDict<string, long>().AddRange(ally.SelectMany(a => a.Bombed).GroupBy(geb => geb.GebID).Select(grp => new Tuple<string, long>(grp.First().Name, grp.Sum(geb => geb.Anzahl)))))));
            DefaultDict<String, DefaultDict<String, long>> gebsGesichtet = new DefaultDict<string, DefaultDict<string, long>>(() => new DefaultDict<string, long>());
            gebsGesichtet.AddRange(gebScans.GroupBy(s => s.Owner.Ally).Select(ally => new Tuple<string, DefaultDict<string, long>>(ally.Key, ally.GroupBy(s => s.Coords.ToString()).Select(g => g.MaxElem(s => s.Time.Ticks)).SelectMany(s => s.Gebs).Aggregate(new DefaultDict<string, long>(), (d, g) => { d[g.name] += g.anz; return d; }))));
            FormatTable(stats, gebsVerloren, "Gebäude", "geb_" + war.id, showpercent: true, backgroundInfo: gebsGesichtet, backgroundFormatter:c => " / "+c.ToString("n0"));

            stats.AppendLine("<h3>Verlorene Ress (Schiffe)</h3>");
            DefaultDict<string, DefaultDict<String, double>> ressVerloren = new DefaultDict<string, DefaultDict<string, double>>();
            foreach (var grp in kbs.SelectMany(kb => kb.DefFleets.Union(kb.AttFleets)).GroupBy(fl => fl.Ally)) {
                ResourceSet rset = grp.SelectMany(fl => fl.Ships).Aggregate(new ResourceSet(), (rs, s) => rs + tkc.Kosten(s.Name, con, DBPrefix) * s.Anzahl_Verlust);
                if (rset.RaidScore <= float.Epsilon)
                    continue;
                ressVerloren.Add(grp.Key, rset.AsDict());
            }
            FormatTable(stats, ressVerloren, "Ress", "ress_" + war.id, showsum:true, showpercent: true);

            stats.Append("Stats erstellt um ").Append(DateTime.Now.ToString()).AppendLine("<br />");

            //Werften pro Ally
            //Planeten ohne Flottenscanner
            //Planeten mit Galascannern
            //Nach Schiffen: Verluste / Spieler
            //Ress durch Bombings verloren
            //Anzahl Schiffe
            //Gesamtzahl Gebäude bei den Gebäudeverlusten dabei schreiben

            //für einzlene Schiffe berechnen welcher Spieler wie viel verloren hat
            //aufhübschen ^^
            return stats.ToString();
        }

        private void GenerateThread(object arg) {
            uint warid = (uint)arg;
            lock (generatorLock) {
                Log.WriteLine(LogLevel.E_NOTICE, "Warstats start: Krieg #" + warid);
                MySqlConnection con = new MySqlConnection(connectionString);
                try {
                    con.Open();
                    MySqlCommand kbSelect = new MySqlCommand(@"SELECT iwid, hash FROM " + DBPrefix + "war_kbs WHERE warid=?warid", con);
                    kbSelect.Parameters.Add("?warid", MySqlDbType.UInt32).Value = warid;
                    MySqlDataReader r = kbSelect.ExecuteReader();
                    List<Tuple<uint, string>> kampfberichte = new List<Tuple<uint, string>>();
                    try {
                        while (r.Read()) {
                            kampfberichte.Add(new Tuple<uint, string>(r.GetUInt32(0), r.GetString(1)));
                        }
                    } finally {
                        r.Close();
                    }
                    MySqlCommand scanSelect = new MySqlCommand(@"SELECT iwid, iwhash FROM " + DBPrefix + "scans WHERE warid=?warid", con);
                    scanSelect.Parameters.Add("?warid", MySqlDbType.UInt32).Value = warid;
                    r = scanSelect.ExecuteReader();
                    List<Tuple<uint, string>> scans = new List<Tuple<uint, string>>();
                    try {
                        while (r.Read()) {
                            scans.Add(new Tuple<uint, string>(r.GetUInt32(0), r.GetString(1)));
                        }
                    } finally {
                        r.Close();
                    }

                    IEnumerable<Kb> kbs = kampfberichte.Select(p => new Kb(p.Item1, p.Item2, con, DBPrefix));
                    WarFilter.War war = kbs.SelectMany(kb => kb.Attackers.Union(kb.Defenders).Select(s => warFilter.getWar(s.Ally, kb.TimeStamp))).First(w => w != null);
                    if (war == null) {
                        Log.WriteLine(LogLevel.E_WARNING, "Warstats ohne Krieg: #" + warid);
                        return;
                    }
                    IEnumerable<IWScan> scs = scans.Select(s => IWScan.LoadXml(con, DBPrefix, warFilter, s.Item1, s.Item2));
                    String stats = GenerateStats(kbs.ToList(), scs.OfType<GebScan>().ToList(), scs.OfType<SchiffScan>().ToList(), war, con);
                    MySqlCommand statsInsert = new MySqlCommand(@"INSERT INTO " + DBPrefix + @"war_stats (id, stats) VALUES (?id, ?stats) on duplicate key update stats=VALUES(stats)", con);
                    statsInsert.Parameters.Add("?id", MySqlDbType.UInt32).Value = war.id;
                    statsInsert.Parameters.Add("?stats", MySqlDbType.Text).Value = stats;
                    statsInsert.ExecuteNonQuery();
                } finally {
                    con.Close();
                }
                Log.WriteLine(LogLevel.E_NOTICE, "Warstats end: Krieg #" + warid);
            }
        }


        public void HandleRequest(ParserRequestMessage msg) {
            new Thread(GenerateThread).Start(uint.Parse(msg[1].AsString));
            msg.AnswerLine("Stats werden generiert (Seite nochmal refreshen, evtl nen Moment warten)!");
            msg.Handled();
        }

        public string Name {
            get { return "warstats"; }
        }
    }


    class Speed {
        public int Gal;
        public int Sol;
    }
    class Coords {
        public int gal;
        public int sys;
        public int pla;
        public Coords() { }
        public Coords(String coords) {
            string[] parts = coords.Split(new char[] { ':' }, 3);
            Check.Cond(parts.Length != 3, "coords sollen im Format gala:sys:pla sein!");
            gal = int.Parse(parts[0]);
            sys = int.Parse(parts[1]);
            pla = int.Parse(parts[2]);
        }
        public override string ToString() {
            return gal + ":" + sys + ":" + pla;
        }
    }
    static class FlugRechner {
        static DefaultDict<string, Speed> speedCache = new DefaultDict<string, Speed>(() => new Speed() { Gal = int.MaxValue, Sol = int.MaxValue }) {
            {"Kamel Z-98 (Hyperraumtransporter Klasse 1)", new Speed() {Gal=4500, Sol=500}},
            {"Waschbär (Hyperraumtransporter Klasse 2)", new Speed() {Gal=4300, Sol=500}},
            {"Zeus", new Speed() {Gal=5600, Sol=200}},
            {"X12 (Carrier)", new Speed() {Gal=4900, Sol=600}},
            /* Hack: gehe davon aus dass die Schiffe transportiert werden */
            {"Stormbringer", new Speed() {Gal=4900, Sol=600}},
            {"Manta", new Speed() {Gal=4900, Sol=600}},
            {"Atombomber", new Speed() {Gal=4900, Sol=600}},
            /* \Hack */
            {"Kronk", new Speed() {Gal=5700, Sol=450}},
            {"Succubus", new Speed() {Gal=6000, Sol=670}},
            {"Gatling", new Speed() {Gal=5900, Sol=750}},
        };
        private static List<Coords> sgCache = new List<Coords>();
        
        public static readonly TimeSpan StargateFlug = TimeSpan.FromMinutes(10);
        public static readonly TimeSpan TransportAnkunft = TimeSpan.FromMinutes(5);
        public static readonly TimeSpan AngriffAnkunft = TimeSpan.FromMinutes(15);
        public static readonly TimeSpan SondierungAnkunft = TimeSpan.FromMinutes(5);
        public static void ReloadCache(String DBPrefix, MySqlConnection con) {
            lock (sgCache) {
                MySqlCommand cmd = new MySqlCommand("SELECT gala, sys, pla FROM " + DBPrefix + "universum WHERE objekttyp='Raumstation'", con);
                MySqlDataReader r = cmd.ExecuteReader();
                sgCache.Clear();
                try {
                    while (r.Read()) {
                        sgCache.Add(new Coords() { gal = r.GetInt32(0), sys = r.GetInt32(1), pla = r.GetInt32(2) });
                    }
                } finally {
                    r.Close();
                }
                sgCache.Sort((c1, c2) => c1.gal < c2.gal ? -1 : c1.gal > c2.gal ? 1 : c1.sys < c2.sys ? -1 : c1.sys > c2.sys ? 1 : (int)c1.pla - (int)c2.pla);
            }
        }
        private static Coords NextSg(Coords c) {
            lock (sgCache) {
                return sgCache.MaxElem(sg => -((sg.gal - c.gal) * (sg.gal - c.gal) + (sg.sys - c.sys) * (sg.sys - c.sys) + (sg.pla - c.pla) * (sg.pla - c.pla)));
            }
        }
        public static TimeSpan SgZeit(String from, String to, String schiff) {
            return SgZeit(new Coords(from), new Coords(to), schiff);
        }
        private static TimeSpan SgZeit(Coords s, Coords d, String schiff) {
            Coords sg_s = NextSg(s);
            Coords sg_d = NextSg(d);
            if (sg_s == null || sg_d == null || sg_s == sg_d)
                return TimeSpan.MaxValue;
            return Zeit(s, sg_s, schiff) + StargateFlug + Zeit(sg_d, d, schiff);
        }

        public static TimeSpan Zeit(String from, String to, String schiff) {
            return Zeit(new Coords(from), new Coords(to), schiff);
        }
        private static TimeSpan Zeit(Coords s, Coords d, String schiff) {
            if (s.gal == d.gal && s.sys == d.sys)
                return TimeSpan.FromSeconds(1500000.0* Math.Log(Math.Abs(d.pla - s.pla) + 6) / speedCache[schiff].Sol);
            int galspeed = speedCache[schiff].Gal;
            if (galspeed == 0)
                return TimeSpan.MaxValue;
            int mod = s.gal != d.gal ? 100 : 5;
            double gal = Math.Abs(s.gal - d.gal);
            double sol = Math.Abs(s.sys - d.sys);
            double pla = Math.Abs(s.pla - d.pla);
            return TimeSpan.FromSeconds((15000000 / galspeed) * Math.Pow(((3000 * gal * gal) / Math.Log(gal + 50) + (mod * sol * Math.Max(3, sol)) / Math.Log(sol + 2) + pla), 0.25));
        }
        public static TimeSpan MinZeit(String from, String to, String schiff) {
            Coords s = new Coords(from);
            Coords d = new Coords(to);
            TimeSpan direkt = Zeit(s, d, schiff);
            TimeSpan sg = SgZeit(s, d, schiff);
            if (direkt < sg)
                return direkt;
            return sg;
        }
        public static TimeSpan MaxZeit(String schiff) {
            return Zeit("1:1:1", "20:199:20", schiff);
        }
    }


}
