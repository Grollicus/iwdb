using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Xml;
using MySql.Data.MySqlClient;
using IRCeX;
using System.Collections;

namespace IWDB.Parser {
	class KBParser:ReportParser {
		WarFilter warFilter;
		TechTreeKostenCache techKostenCache;
		public KBParser(NewscanHandler newscanHandler, WarFilter warFilter, TechTreeKostenCache tkc)
			: base(newscanHandler) {
				AddPatern(@"http://www\.icewars\.de/portal/kb/de/kb\.php\?id=(\d+)&md_hash=([a-z0-9A-Z]{32})");
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

		public bool SaveAsWarKb(uint warID, MySqlConnection con, String DBPrefix, TechTreeKostenCache tkc) {
			if(saveMode != KbSaveMode.None)
				throw new InvalidOperationException("Versuche KB als KriegsKB zu speichern obwohl er schon gespeichert ist!");

			MySqlCommand cmd = new MySqlCommand(@"INSERT IGNORE INTO " + DBPrefix + "war_kbs (iwid,hash,timestamp,att,attally,def,defally,attvalue,attloss,defvalue,defloss,raidvalue,bombvalue, attwin, start, dst, warid) VALUES (?iwid,?hash,?timestamp,?att,?attally,?def,?defally,?attvalue,?attloss,?defvalue,?defloss,?raidvalue,?bombvalue,?attwin,?start,?dst,?warid)", con);
			cmd.Parameters.Add("?iwid", MySqlDbType.UInt32).Value = iwid;
			cmd.Parameters.Add("?hash", MySqlDbType.String).Value = hash;
			cmd.Parameters.Add("?timestamp", MySqlDbType.UInt32).Value = TimeStamp;

			List<Pair<String, String>> att = Attackers;
			List<Pair<String, String>> def = Defenders;

			cmd.Parameters.Add("?att", MySqlDbType.String).Value = att.Select(p => p.Item1).Distinct().Aggregate((x, y) => x+ ", " + y);
			cmd.Parameters.Add("?attally", MySqlDbType.String).Value = att.Select(p => p.Item2).Distinct().Aggregate((x, y) => x + ", " + y);
			cmd.Parameters.Add("?def", MySqlDbType.String).Value = def.Select(p => p.Item1).Distinct().Aggregate((x, y) => x + ", " + y);
			cmd.Parameters.Add("?defally", MySqlDbType.String).Value = def.Select(p => p.Item2).Distinct().Aggregate((x, y) => x + ", " + y);

			List<Tuple<String, uint, uint, uint, uint>> attShips = AttShips;
			List<Tuple<String, uint, uint, uint, uint>> defShips = DefShips;

			cmd.Parameters.Add("?attvalue", MySqlDbType.UInt32).Value = (uint)(attShips.Aggregate(new ResourceSet(), (rs, ship) => rs + ship.Item3 * tkc.Query(ship.Item1, con, DBPrefix)).RaidScore);
			cmd.Parameters.Add("?attloss", MySqlDbType.UInt32).Value = attresslost.RaidScore;
			cmd.Parameters.Add("?defvalue", MySqlDbType.UInt32).Value = (uint)(defShips.Aggregate(new ResourceSet(), (rs, ship) => rs + ship.Item3 * tkc.Query(ship.Item1, con, DBPrefix)).RaidScore);
			cmd.Parameters.Add("?defloss", MySqlDbType.UInt32).Value = defresslost.RaidScore;
			cmd.Parameters.Add("?raidvalue", MySqlDbType.UInt32).Value = pluenderung.RaidScore;
			cmd.Parameters.Add("?bombvalue", MySqlDbType.UInt32).Value = Bombed.Aggregate((uint)0, (n, tp) => n + tp.Item3);

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
		public List<Pair<String, String>> Attackers {
			get {
				List<Pair<String, String>> ret = new List<Pair<string, string>>();
				foreach(XmlNode n in xml.SelectNodes("flotten_att")) {
					ret.Add(new Pair<string, string>(n["user"]["name"].Attributes["value"].InnerText, n["user"]["allianz_tag"].Attributes["value"].InnerText));
				}
				return ret;
			}
		}
		public List<Pair<String, String>> Defenders {
			get {
				List<Pair<String, String>> ret = new List<Pair<string, string>>();
				ret.Add(new Pair<string, string>(xml["pla_def"]["user"]["name"].Attributes["value"].InnerText, xml["pla_def"]["user"]["allianz_tag"].Attributes["value"].InnerText));
				foreach(XmlNode n in xml.SelectNodes("flotten_def")) {
					ret.Add(new Pair<string, string>(n["user"]["name"].Attributes["value"].InnerText, n["user"]["allianz_tag"].Attributes["value"].InnerText));
				}
				return ret;
			}
		}
		public List<Tuple<String, uint, uint, uint, uint>> AttShips {
			get {
				List<Tuple<String, uint, uint, uint, uint>> ret = new List<Tuple<string, uint, uint, uint, uint>>();
				foreach(XmlNode node in xml.SelectNodes("flotten_att")) {
					foreach(XmlNode n in node.SelectNodes("user/schiffe/schifftyp")) {
						ret.Add(new Tuple<string,uint,uint,uint,uint>(
							n["name"].Attributes["value"].InnerText, 
							uint.Parse(n["id"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText), 
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						));
					}
				}
				return ret;
			}
		}
		public List<Tuple<String, uint, uint, uint, uint>> DefShips {
			get {
				List<Tuple<String, uint, uint, uint, uint>> ret = new List<Tuple<string, uint, uint, uint, uint>>();
				List<XmlNode> l = new List<XmlNode>();

				foreach(XmlNode n in xml.SelectNodes("flotten_def"))
					l.Add(n);
				l.Add(xml.SelectSingleNode("pla_def"));

				foreach(XmlNode node in l) {
					foreach(XmlNode n in node.SelectNodes("user/schiffe/schifftyp")) {
						ret.Add(new Tuple<string, uint, uint, uint, uint>(
							n["name"].Attributes["value"].InnerText,
							uint.Parse(n["id"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						));
					}
					foreach(XmlNode n in node.SelectNodes("user/defence/defencetyp")) {
						ret.Add(new Tuple<string, uint, uint, uint, uint>(
							n["name"].Attributes["value"].InnerText,
							uint.Parse(1000+n["id"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_start"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_ende"].Attributes["value"].InnerText),
							uint.Parse(n["anzahl_verlust"].Attributes["value"].InnerText)
						));
					}
				}
				return ret;
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
		public IEnumerable<Tuple<String, uint, uint>> Bombed {
			get {
				foreach(XmlNode n in xml.SelectNodes("bomben/geb_zerstoert/geb")) {
					yield return new Tuple<String, uint, uint>(n["name"].Attributes["value"].InnerText, uint.Parse(n["id"].Attributes["value"].InnerText), uint.Parse(n["anzahl"].Attributes["value"].InnerText)); 
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
	}

	class TechTreeKostenCache {
		protected Dictionary<String, ResourceSet> kostenCache = new Dictionary<string, ResourceSet>();
		public void Clear() {
			lock(kostenCache) {
				kostenCache.Clear();
			}
		}
		public ResourceSet Query(String schiffsName, MySqlConnection con, String DBPrefix) {
			lock(kostenCache) {
				ResourceSet ret = null;
				if(kostenCache.TryGetValue(schiffsName, out ret))
					return ret;
				MySqlCommand cmd = new MySqlCommand(@"SELECT Dauer, bauE, bauS, bauC, bauV, bauEis, bauW, bauEn, bauCr, bauBev FROM " + DBPrefix + @"techtree_items AS techtree_items INNER JOIN " + DBPrefix + "techtree_stufen AS techtree_stufen ON techtree_items.ID=techtree_stufen.ItemID WHERE techtree_items.Name=?name AND techtree_items.type <> 'for'", con);
				cmd.Parameters.Add("?name", MySqlDbType.String).Value = schiffsName;
				cmd.Prepare();
				MySqlDataReader r = cmd.ExecuteReader();
				try {
					ret = new ResourceSet();
					if(!r.Read()) {
						kostenCache.Add(schiffsName, ret);
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
					kostenCache.Add(schiffsName, ret);
				} finally {
					r.Close();
				}
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
		public TechTreeKostenCache TechKostenCache;
		public WarFilter(String DBPrefix, MySqlConnection con, TechTreeKostenCache tkc) {
			this.DBPrefix = DBPrefix;
			this.con = con;
			this.TechKostenCache = tkc;
			Reload();
		}

		protected void Reload() {
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

		public void HandleRequest(ParserRequestMessage msg) {
			try {
				Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh");
				IRCeX.Log.WriteLine("MySqlOpen: WarRefresh");
				con.Open();
				Reload();
				lock(wars) {
					msg.AnswerLine(DateTime.Now.ToString() + " " + wars.Count + " Kriege neu geladen & Techtree-Cache geleert!");
				}
				Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh:Raid");
				uint oldWar = 0, oldRaid = 0;
				List<Kb> kbs = new List<Kb>();
				MySqlCommand cmd_raid = new MySqlCommand(@"SELECT iwid, hash FROM " + DBPrefix + @"raidberichte", con);
				MySqlDataReader r = cmd_raid.ExecuteReader();
				try {
					while(r.Read()) {
						oldRaid++;
						kbs.Add(new Kb(r.GetUInt32(0), r.GetString(1), KbSaveMode.Raid, con, DBPrefix));
					}
				} finally {
					r.Close();
				}

				MySqlCommand cmd_war = new MySqlCommand(@"SELECT iwid, hash FROM " + DBPrefix + @"war_kbs", con);
				r = cmd_war.ExecuteReader();
				try {
					while(r.Read()) {
						oldWar++;
						kbs.Add(new Kb(r.GetUInt32(0), r.GetString(1), KbSaveMode.War, con, DBPrefix));
					}
				} finally {
					r.Close();
				}

				uint newWar = 0, newRaid = 0;
				foreach(Kb kb in kbs) {
					if(((newWar + newRaid) % 100) == 0) {
						Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh: .");
					}
					kb.ReadKbFromXml(con, DBPrefix);
					War war = null;
					foreach(String tag in kb.AllyTags) {
						war = getWar(tag, kb.TimeStamp);
						if(war != null)
							break;
					}
					kb.RemoveFromDB(con, DBPrefix);
					if(war != null) {
						newWar++;
						kb.SaveAsWarKb(war.id, con, DBPrefix, TechKostenCache);
					} else {
						newRaid++;
						kb.SaveAsRaid(con, DBPrefix);
					}
				}
				msg.AnswerLine(DateTime.Now.ToString() + " "+ newWar + " Kriegs- (zuvor " + oldWar + ") und " + newRaid + " Raid-KBs (" + oldRaid + " zuvor) neu berechnet!");

				kbs.Clear();

				Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh:Scan");
				List<Tuple<uint, uint, string>> scans = new List<Tuple<uint, uint, string>>();
				MySqlCommand cmd_scans = new MySqlCommand(@"SELECT id, iwid, iwhash FROM " + DBPrefix + "scans", con);
				r = cmd_scans.ExecuteReader();
				try {
					while(r.Read()) {
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
				foreach(Tuple<uint, uint, string> tpl in scans) {
					if(((gebscan_cnt + schiffscan_cnt) % 100) == 0) {
						Log.WriteLine(LogLevel.E_NOTICE, "WarRefresh: .");
					}
					flotten.Clear();
					cmd_fl.Parameters["?scanid"].Value = tpl.Item1;
					cmd_flDel.Parameters["?scanid"].Value = tpl.Item1;
					cmd_scanDel.Parameters["?scanid"].Value = tpl.Item1;
					r = cmd_fl.ExecuteReader();
					try {
						while(r.Read()) {
							flotten.Append(r.GetUInt32(0));
							flotten.Append(", ");
						}
					} finally {
						r.Close();
					}
					if(flotten.Length > 0) {
						flotten.Length -= 2;
						MySqlCommand cmd_shipDel = new MySqlCommand("DELETE FROM " + DBPrefix + "scans_flotten_schiffe WHERE flid IN ("+flotten.ToString()+")", con);
						cmd_shipDel.ExecuteNonQuery();
					}
					cmd_flDel.ExecuteNonQuery();
					cmd_scanDel.ExecuteNonQuery();
					String url = String.Format("http://www.icewars.de/portal/kb/de/sb.php?id={0}&md_hash={1}&typ=xml", tpl.Item2, tpl.Item3);
					XmlNode xml = IWCache.Query(url, con, DBPrefix);
					switch(xml.SelectSingleNode("scann/scann_typ/id").InnerText) {
						case "2": { //Sondierung (Gebäude/Ress)
								GebScan s = new GebScan(tpl.Item2, tpl.Item3);
								s.LoadXml(xml, con, DBPrefix, this);
								s.ToDB(con, DBPrefix, TechKostenCache);
								gebscan_cnt++;
							}
							break;
						case "3": { //Sondierung (Schiffe/Def/Ress)
								SchiffScan s = new SchiffScan(tpl.Item2, tpl.Item3);
								s.LoadXml(xml, con, DBPrefix, this);
								s.ToDB(con, DBPrefix, TechKostenCache);
								schiffscan_cnt++;
							} break;
					}
				}
				msg.Answer(DateTime.Now.ToString() + " " + gebscan_cnt + " Gebscans und " + schiffscan_cnt + " Schiffscans neu eingelesen!");
			} finally {
				try {
					msg.Handled();
				} finally {
					IRCeX.Log.WriteLine("MySqlClose: WarRefresh");
					con.Close();
				}
			}
		}

		public string Name {
			get { return "WarFilter"; }
		}
	}

	static class WarStats {
		static void EvaluateKb(Kb kb) {
			//Es gibt eine Tabelle db_war_stats wo die Stats rein sollen. HF -_-
		}
	}
}
