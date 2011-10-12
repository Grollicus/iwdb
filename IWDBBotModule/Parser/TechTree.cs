using System;
using System.Collections.Generic;
using System.Text;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using System.Xml;

namespace IWDB.Parser {
	/*
	 * 4 Typen von Gebäuden:
	 *		echte Stufengebäude => Kolozentrum (erkennbar an dem Text "Stufengebäude" nach dem Namen) - jede Stufe als einzelnes Geb eingetragen (mit Suffix "Stufe 123")
	 *		halbechte Stufengebäude => Lager (haben mehrere Stufen, jedoch unterscheidet IW da nicht)
	 *		unechte Stufengebäude => FLab => können wie echte behandelt werden (haben mehrere Stufen ähnlich wie halbechte, jedoch sind einige Stufen gleich)
	 *		normale Gebäude => Eisenmine => Stufengebäude mit nur 1 Stufe
	 * Tabelle techtree_items ID|Class (stufengeb, kein stufengeb)|global|Type (geb, for usw.)|Gebiet|Name|BenPlanityp|BenKolotyp|Beschreibung || primary ID, unique Name&type
	 * Tabelle techtree_stufen
	 * Tabelle techtree_reqs ItemID|RequiresID||Key requires, Unique ItemID&RequiresID
	 * 
	 * TODO: "Dieses Gebäude benötigt weitere komplexe Voraussetzungen"
	 * TODO: Globale Gebäude
	 * 
	 * TODO: In den Gebäudeinfos werden globale Effekte (Staatsform, Genetik) berücksichtigt. Rausrechnen!
	 */

	//TODO: Parser umbauen so dass sie auch Tausendertrennzeichen beherrschen, zumindest die grundlegenden wie [,. ]
	class GebäudeinfoParser : ReportParser {
        public GebäudeinfoParser(NewscanHandler newscanHandler) : base(newscanHandler) { AddPatern(@"Gebäudeinfo:[\s\S]+?Farbenlegende:", PatternFlags.Firefox); }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match gebinfo in matches) {
				try {
					TechtreeItem item = Gebäude.Parse(gebinfo.Value, DBPrefix);
					if (item == null) {
						resp.RespondError("Bei einem Gebäude ist das Einlesen fehlgeschlagen!");
						continue;
					}
					item.WriteToDB(con);
					resp.Respond("Gebäude " + item.Name + " eingelesen.\n");
				} catch (FormatException) {
					resp.RespondError("Ein Formatfehler ist beim Einlesen eines Gebäudes aufgetreten! Sind die Tausendertrennzeichen richtig eingestellt?");
					continue;
				}
				
			}
		}
	}
	class ForschungsinfoParser:ReportParser {
        public ForschungsinfoParser(NewscanHandler newscanHandler)
            : base(newscanHandler) {
            AddPatern(@"Forschungsinfo:\s+(.+)[\s\S]+?Farbenlegende:", PatternFlags.Firefox);
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySql.Data.MySqlClient.MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match forschungsInfo in matches) {
				try {
					TechtreeItem item = Forschung.Parse(forschungsInfo.Value, DBPrefix);
					if (item == null) {
						resp.RespondError("Eine Forschung konnte nicht richtig erkannt werden! Bitte als Bug melden!");
						continue;
					}
					item.WriteToDB(con);
					resp.Respond("Forschung " + item.Name + " eingelesen.\n");
				} catch (FormatException) {
					resp.RespondError("Ein Formatfehler ist beim Einlesen einer Forschung aufgetreten! Sind die Tausendertrennzeichen richtig eingestellt?");
					continue;
				}
			}
		}
	}
	class SchiffsinfoParser:ReportParser {
		public SchiffsinfoParser(NewscanHandler newscanHandler)
            : base(newscanHandler) {
            AddPatern(@"Schiffinfo:\s+([a-zA-Z0-9 äöü()\-]+)\n
[\s\S]+
Kosten\s+([\s\S]*?)\n
Dauer\s+(" + IWZeitspanne + @")\n
Voraussetzungen\sForschungen\s+((?:\(.+\))*)\n
(?:aufrüstbar\szu.*\n)?
benötigt\sWerften\s+([\s\S]+?)\n
mögliche\sAktionen([\s\S]+?)\n
Daten\n
Geschwindigkeit\sSol\s+(\d+)\n
Geschwindigkeit\sGal\s+(\d+)\n
(?:.*Galaxie\sverlassen.*\n)?
Verbrauch\schem.\sElemente\s+(\d+)\n
Verbrauch\sEnergie\s+(\d+)\n", PatternFlags.Firefox);
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match m in matches) {
				TechtreeItem item = new Schiff(m, DBPrefix);
				item.WriteToDB(con);
				resp.Respond("Schiffsinfo eingelesen!\n");
			}
		}
	}
class ForschungsübersichtParser:ReportParser {
    public ForschungsübersichtParser(NewscanHandler newscanHandler) : base(newscanHandler) { AddPatern(@"Erforschte\sForschungen\n([\s\S]+)", PatternFlags.All); }
    public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match m in matches) {

				MySqlCommand cleanup = new MySqlCommand(@"DELETE FROM techtree_useritems
		USING (" + DBPrefix + @"techtree_useritems AS techtree_useritems) INNER JOIN (" + DBPrefix + @"techtree_items AS techtree_items) ON techtree_items.ID=techtree_useritems.itemid
		WHERE techtree_useritems.uid=?uid AND techtree_items.type = 'for'", con);
				cleanup.Parameters.Add("?uid", MySqlDbType.UInt32).Value = victimID;
				cleanup.Prepare();
				cleanup.ExecuteNonQuery();

				String[] parts = m.Groups[1].Value.Split('\n');
				MySqlCommand idQry = new MySqlCommand(@"SELECT ID FROM " + DBPrefix + @"techtree_items WHERE Name=?name", con);
				idQry.Parameters.Add("?name", MySqlDbType.String);
				idQry.Prepare();

				MySqlCommand idInsert = new MySqlCommand(@"INSERT IGNORE INTO " + DBPrefix + @"techtree_useritems (uid, itemid, count) VALUES (?uid, ?itemid, 1)", con);
				idInsert.Parameters.Add("?uid", MySqlDbType.UInt32).Value = victimID;
				idInsert.Parameters.Add("?itemid", MySqlDbType.UInt32);
				idInsert.Prepare();

				foreach (String forschung in parts) {
					idQry.Parameters["?name"].Value = forschung;
					object res = idQry.ExecuteScalar();
					if (res == null)
						continue;
					uint id = (uint)res;
					idInsert.Parameters["?itemid"].Value = id;
					idInsert.ExecuteNonQuery();
				}
			}
			resp.Respond("Forschungsübersicht eingelesen!");
		}
	}
class GebäudeübersichtParser:ReportParser {
		struct Kolo {
			public Kolo(Match m) {
				gala = uint.Parse(m.Groups[1].Value);
				sys = uint.Parse(m.Groups[2].Value);
				pla = uint.Parse(m.Groups[3].Value);
				typ = m.Groups[4].Value;
			}
			public uint gala;
			public uint sys;
			public uint pla;
			public string typ;
			public String Coords { get { return gala + ":" + sys + ":" + pla; } }
		}
	public GebäudeübersichtParser(NewscanHandler newscanHandler) : base(newscanHandler) {
        AddPatern(@"Artefaktübersicht\s*\nGebäudeübersicht\n\s*\nGebäudeübersicht\n([\s\S]+)", PatternFlags.All);
    }
    public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match outerMatch in matches) {
				MySqlCommand cleanup = new MySqlCommand("DELETE FROM "+DBPrefix+"techtree_useritems WHERE uid=?uid AND coords <> ''", con);
				cleanup.Parameters.Add("?uid", MySqlDbType.UInt32).Value=victimID;
				cleanup.Prepare();
				cleanup.ExecuteNonQuery();

				MySqlCommand idQry = new MySqlCommand(@"SELECT ID FROM " + DBPrefix + "techtree_items WHERE Name=?name", con);
				idQry.Parameters.Add("?name", MySqlDbType.String);
				idQry.Prepare();

				MySqlCommand insert = new MySqlCommand(@"INSERT INTO "+DBPrefix+"techtree_useritems (uid, itemid, count, coords) VALUES (?uid, ?itemid, ?count, ?coords)", con);
				insert.Parameters.Add("?uid", MySqlDbType.UInt32).Value = victimID;
				insert.Parameters.Add("?itemid", MySqlDbType.UInt32);
				insert.Parameters.Add("?count", MySqlDbType.UInt32);
				insert.Parameters.Add("?coords", MySqlDbType.Enum);
				insert.Prepare();

				MatchCollection mc = Regex.Matches(outerMatch.Groups[1].Value, @"([a-zA-ZäöüÄÖÜ& ]+)\n
	((?:\s+\d+:\d+:\d+\n\(.+\))+)\n
	((?:.+?(?:\s\d*)+\n)+)", RegexOptions.IgnorePatternWhitespace);

				foreach (Match m in mc) {
					MatchCollection koloMatches = Regex.Matches(m.Groups[2].Value, @"(\d+):(\d+):(\d+)\s+\((.+)\)");
					List<Kolo> kolos = new List<Kolo>();
					foreach (Match koloMatch in koloMatches) {
						kolos.Add(new Kolo(koloMatch));
					}
					MatchCollection itemMatches = Regex.Matches(m.Groups[3].Value, @"(.+?)((?:\s\d*)+)\n");
					foreach (Match itemMatch in itemMatches) {
						String item = itemMatch.Groups[1].Value;
						idQry.Parameters["?name"].Value = item;
						object ItemID = idQry.ExecuteScalar();
						if (ItemID == null)
							continue;

						insert.Parameters["?itemid"].Value = (uint)ItemID;
						MatchCollection anzMatches = Regex.Matches(itemMatch.Groups[2].Value, @"\s(\d*)");
						int i = 0;
						foreach (Match anzahlMatch in anzMatches) {
							if (anzahlMatch.Groups[1].Length == 0)
								continue;
							uint anz = uint.Parse(anzahlMatch.Groups[1].Value);
							insert.Parameters["?count"].Value = anz;
							insert.Parameters["?coords"].Value = kolos[i].Coords;
							insert.ExecuteNonQuery();
							++i;
						}
					}
				}
				
			}
			resp.Respond("Gebäudeübersicht eingelesen!");
		}
	}
abstract class TechtreeItem {

		protected enum ItemClass:byte {
			Normal = 0,
			UnechtesStufengeb = 1,
			Stufengeb = 2,
		}
		
		protected String name;
		protected String gebiet;
		protected Boolean isGlobal;
		protected ItemClass cls;
		protected String type;
		protected String beschreibung;
		protected int maxLevel;
		protected Dictionary<int, TechtreeItemStufe> stufen;
		protected List<String> benForschungen;
		protected List<String> benGebs;
		protected String benKolotyp;
		protected String benPlanityp;
		protected List<String> zerstörungDurch;
		protected List<String> ermForschungen;
		protected List<String> ermGebs;
		protected List<String> ermDef;

	protected String DBPrefix;

		protected TechtreeItem(String type, String DBPrefix) {
			this.type = type;
			stufen = new Dictionary<int, TechtreeItemStufe>();
			benForschungen = new List<string>();
			benGebs = new List<string>();
            benPlanityp = "";
            benKolotyp = "";
			zerstörungDurch = new List<string>();
			ermForschungen = new List<string>();
			ermGebs = new List<string>();
			ermDef = new List<string>();
			maxLevel = 0;
			gebiet = "";
			isGlobal = false;
			this.DBPrefix = DBPrefix;
		}

		public String Name { get { return name; } }
		public String Type { get { return type; } }
		public void WriteToDB(MySqlConnection con) {
			MySqlTransaction transaction = con.BeginTransaction();
#if !DEBUG
			try {
#endif
				uint id = getItemID(name, type, con);
				MySqlCommand update = new MySqlCommand("UPDATE " + DBPrefix + @"techtree_items SET
				Class=?cls, MaxLevel=?maxlvl, global=?global, Gebiet=?gebiet, BenPlanityp=?planityp, BenKolotyp=?kolotyp, Beschreibung=?desc WHERE ID=?id", con);
				update.Parameters.Add("?cls", MySqlDbType.Byte).Value = (byte)cls;
				update.Parameters.Add("?maxlvl", MySqlDbType.UInt16).Value = maxLevel;
				update.Parameters.Add("?global", MySqlDbType.Int32).Value = isGlobal ? 1 : 0;
				update.Parameters.Add("?gebiet", MySqlDbType.String).Value = gebiet;
				update.Parameters.Add("?planityp", MySqlDbType.String).Value = benPlanityp;
				update.Parameters.Add("?kolotyp", MySqlDbType.String).Value = benKolotyp;
				update.Parameters.Add("?desc", MySqlDbType.Text).Value = beschreibung;
				update.Parameters.Add("?id", MySqlDbType.UInt32).Value = id;
				update.Prepare();
				update.ExecuteNonQuery();

				#region StufenUpdate
				MySqlCommand stufenUpdate = new MySqlCommand("INSERT INTO " + DBPrefix + @"techtree_stufen
				(ItemID, Stufe, Dauer, bauE, bauS, bauC, bauV, bauEis, bauW, bauEn, bauCr, bauBev, bauFP,
					E, S, C, V, Eis, W, En, Cr, Bev, FP, Sonstiges)
		 VALUES (?itemID, ?stufe, ?dauer, ?bauE, ?bauS, ?bauC, ?bauV, ?bauEis, ?bauW, ?bauEn, ?bauCr, ?bauBev, ?bauFP,
					?E, S, ?C, ?V, ?Eis, ?W, ?En, ?Cr, ?Bev, ?FP, ?Sonstiges)
		 ON DUPLICATE KEY UPDATE
			ItemID=VALUES(ItemID), Stufe=VALUES(Stufe), Dauer=VALUES(Dauer), bauE=VALUES(bauE), bauS=VALUES(bauS),
			bauC=VALUES(bauC), bauV=VALUES(bauV), bauEis=VALUES(bauEis), bauW=VALUES(bauW), bauEn=VALUES(bauEn),
			bauCr=VALUES(bauCr), bauBev=VALUES(bauBev), bauFP=VALUES(bauFP), E=VALUES(E), S=VALUES(S), C=VALUES(C),
			V=VALUES(V), Eis=VALUES(Eis), W=VALUES(W), En=VALUES(En), Cr=VALUES(Cr), Bev=VALUES(Bev), FP=VALUES(FP),
			Sonstiges=VALUES(Sonstiges)", con);
				stufenUpdate.Parameters.Add("?itemID", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?stufe", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?dauer", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauE", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauS", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauC", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauV", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauEis", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauW", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauEn", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauCr", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauBev", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?bauFP", MySqlDbType.UInt32);
				stufenUpdate.Parameters.Add("?E", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?S", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?C", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?V", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?Eis", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?W", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?En", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?Cr", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?Bev", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?FP", MySqlDbType.Int32);
				stufenUpdate.Parameters.Add("?Sonstiges", MySqlDbType.String);
				stufenUpdate.Prepare();
				foreach (TechtreeItemStufe s in stufen.Values) {
					stufenUpdate.Parameters["?itemID"].Value = id;
					stufenUpdate.Parameters["?stufe"].Value = s.Stufe;
					stufenUpdate.Parameters["?dauer"].Value = s.Dauer;
					stufenUpdate.Parameters["?bauE"].Value = s.BauKosten.Eisen;
					stufenUpdate.Parameters["?bauS"].Value = s.BauKosten.Stahl;
					stufenUpdate.Parameters["?bauC"].Value = s.BauKosten.Chemie;
					stufenUpdate.Parameters["?bauV"].Value = s.BauKosten.VV4A;
					stufenUpdate.Parameters["?bauEis"].Value = s.BauKosten.Eis;
					stufenUpdate.Parameters["?bauW"].Value = s.BauKosten.Wasser;
					stufenUpdate.Parameters["?bauEn"].Value = s.BauKosten.Energie;
					stufenUpdate.Parameters["?bauCr"].Value = s.BauKosten.Credits;
					stufenUpdate.Parameters["?bauBev"].Value = s.BauKosten.Bev;
					stufenUpdate.Parameters["?bauFP"].Value = s.BauKosten.FP;
					stufenUpdate.Parameters["?E"].Value = s.laufendeEffekte.Eisen;
					stufenUpdate.Parameters["?S"].Value = s.laufendeEffekte.Stahl;
					stufenUpdate.Parameters["?C"].Value = s.laufendeEffekte.Chemie;
					stufenUpdate.Parameters["?V"].Value = s.laufendeEffekte.VV4A;
					stufenUpdate.Parameters["?Eis"].Value = s.laufendeEffekte.Eis;
					stufenUpdate.Parameters["?W"].Value = s.laufendeEffekte.Wasser;
					stufenUpdate.Parameters["?En"].Value = s.laufendeEffekte.Energie;
					stufenUpdate.Parameters["?Cr"].Value = s.laufendeEffekte.Credits;
					stufenUpdate.Parameters["?Bev"].Value = s.laufendeEffekte.Bev;
					stufenUpdate.Parameters["?FP"].Value = s.laufendeEffekte.FP;
					stufenUpdate.Parameters["?Sonstiges"].Value = s.laufendeEffekte.Sonstiges;
					stufenUpdate.ExecuteNonQuery();
				}
				#endregion

				MySqlCommand requirementsUpdate = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + @"techtree_reqs
				(ItemID, RequiresID) VALUES (?item, ?req)", con);
				requirementsUpdate.Parameters.Add("?item", MySqlDbType.UInt32);
				requirementsUpdate.Parameters.Add("?req", MySqlDbType.UInt32);
				requirementsUpdate.Prepare();

				StringBuilder reqIDs = new StringBuilder();
				foreach (String req in benForschungen) {
					requirementsUpdate.Parameters["?item"].Value = id;
					uint rid = getItemID(req, "for", con);
					reqIDs.Append(rid);
					reqIDs.Append(", ");
					requirementsUpdate.Parameters["?req"].Value = rid;
					requirementsUpdate.ExecuteNonQuery();
				}
				foreach (String req in benGebs) {
					requirementsUpdate.Parameters["?item"].Value = id;
					uint rid = getItemID(req, "geb", con);
					reqIDs.Append(rid);
					reqIDs.Append(", ");
					requirementsUpdate.Parameters["?req"].Value = rid;
					requirementsUpdate.ExecuteNonQuery();
				}
				foreach (String erm in ermForschungen) {
					requirementsUpdate.Parameters["?item"].Value = getItemID(erm, "for", con);
					requirementsUpdate.Parameters["?req"].Value = id;
					requirementsUpdate.ExecuteNonQuery();
				}
				foreach (String erm in ermGebs) {
					requirementsUpdate.Parameters["?item"].Value = getItemID(erm, "geb", con);
					requirementsUpdate.Parameters["?req"].Value = id;
					requirementsUpdate.ExecuteNonQuery();
				}
				foreach (String erm in ermDef) {
					requirementsUpdate.Parameters["?item"].Value = getItemID(erm, "geb", con);
					requirementsUpdate.Parameters["?req"].Value = id;
					requirementsUpdate.ExecuteNonQuery();
				}

				uint depth;
				if (reqIDs.Length == 0) {
					depth = 0;
				} else {
					reqIDs.Length = reqIDs.Length - 2;
					String reqs = reqIDs.ToString();
					MySqlCommand depthQry = new MySqlCommand(@"SELECT MAX(depth) FROM " + DBPrefix + @"techtree_items WHERE ID IN (" + reqs + ")", con);
					depth = (uint)depthQry.ExecuteScalar() + 1;
				}
				MySqlCommand depthUpdate = new MySqlCommand(@"UPDATE " + DBPrefix + @"techtree_items SET depth=?depth WHERE ID=?id", con);
				depthUpdate.Parameters.Add("?depth", MySqlDbType.UInt32).Value = depth;
				depthUpdate.Parameters.Add("?id", MySqlDbType.UInt32).Value = id;
				depthUpdate.Prepare();
				depthUpdate.ExecuteNonQuery();

				transaction.Commit();
#if !DEBUG
			} catch (Exception e) {
				transaction.Rollback();
				throw e;
			}
#endif
		}
		private uint getItemID(String name, String type, MySqlConnection con) {
			MySqlCommand cmd = new MySqlCommand("SELECT ID FROM " + DBPrefix + "techtree_items WHERE name=?name AND type=?type", con);
			cmd.Parameters.Add("?name", MySqlDbType.String).Value = name;
			cmd.Parameters.Add("?type", MySqlDbType.String).Value = type;
			cmd.Prepare();
			object obj = cmd.ExecuteScalar();
			if (obj != null) {
				return (uint)obj;
			}
			MySqlCommand insert = new MySqlCommand("INSERT INTO " + DBPrefix + "techtree_items (name, type) VALUES (?name, ?type)", con);
			insert.Parameters.Add("?name", MySqlDbType.String).Value = name;
			insert.Parameters.Add("?type", MySqlDbType.String).Value = type;
			insert.Prepare();
			insert.ExecuteNonQuery();
			return (uint)insert.LastInsertedId;
		}
		protected void BedingungsParser(String bedingung, List<string> Ziel) {
			StringBuilder sb = new StringBuilder();
			int klammerlvl = 0;
			foreach (char c in bedingung) {
				if (c == '(') {
					++klammerlvl;
				} else if (c == ')') {
					--klammerlvl;
					if (klammerlvl == 0) {
						String item = sb.ToString(1, sb.Length - 1);
						sb.Length = 0;
						if (item != "Gruppe") {
							Ziel.Add(item);
						}
					}
				}
				if (klammerlvl == 0)
					continue;
				sb.Append(c);
			}
			
		}
	}
	class ResourceSet {
		public float Eisen;
		public float Stahl;
		public float Chemie;
		public float VV4A;
		public float Eis;
		public float Wasser;
		public float Energie;
		public float Credits;
		public float Bev;
		public float FP;
		public String Sonstiges;
		public TimeSpan Zeit;

		public ResourceSet() {
			Eisen = Stahl = Chemie = VV4A = Eis = Wasser = Energie = Credits = Bev = FP = 0;
			Sonstiges = "";
			Zeit = TimeSpan.Zero;
		}
		public static ResourceSet operator -(ResourceSet rs1, ResourceSet rs2) {
			ResourceSet ret = new ResourceSet();
			ret.Eisen = rs1.Eisen-rs2.Eisen;
			ret.Stahl = rs1.Stahl-rs2.Stahl;
			ret.Chemie = rs1.Chemie-rs2.Chemie;
			ret.VV4A = rs1.VV4A-rs2.VV4A;
			ret.Eis = rs1.Eis-rs2.Eis;
			ret.Wasser = rs1.Wasser-rs2.Wasser;
			ret.Energie = rs1.Energie-rs2.Energie;
			ret.Credits = rs1.Credits-rs2.Credits;
			ret.Bev = rs1.Bev-rs2.Bev;
			ret.FP = rs1.FP-rs2.FP;
			ret.Zeit = rs1.Zeit - rs2.Zeit;
			return ret;
		}
		public static ResourceSet operator +(ResourceSet rs1, ResourceSet rs2) {
			ResourceSet ret = new ResourceSet();
			ret.Eisen = rs1.Eisen + rs2.Eisen;
			ret.Stahl = rs1.Stahl + rs2.Stahl;
			ret.Chemie = rs1.Chemie + rs2.Chemie;
			ret.VV4A = rs1.VV4A + rs2.VV4A;
			ret.Eis = rs1.Eis + rs2.Eis;
			ret.Wasser = rs1.Wasser + rs2.Wasser;
			ret.Energie = rs1.Energie + rs2.Energie;
			ret.Credits = rs1.Credits + rs2.Credits;
			ret.Bev = rs1.Bev + rs2.Bev;
			ret.FP = rs1.FP + rs2.FP;
			ret.Zeit = rs1.Zeit + rs2.Zeit;
			return ret;
		}
		public static ResourceSet operator *(ResourceSet rs1, uint scalar) {
			ResourceSet ret = new ResourceSet();
			ret.Eisen = rs1.Eisen * scalar;
			ret.Stahl = rs1.Stahl * scalar;
			ret.Chemie = rs1.Chemie * scalar;
			ret.VV4A = rs1.VV4A * scalar;
			ret.Eis = rs1.Eis * scalar;
			ret.Wasser = rs1.Wasser * scalar;
			ret.Energie = rs1.Energie * scalar;
			ret.Credits = rs1.Credits * scalar;
			ret.Bev = rs1.Bev * scalar;
			ret.FP = rs1.FP * scalar;
			ret.Zeit = TimeSpan.FromSeconds(rs1.Zeit.TotalSeconds * scalar);
			return ret;
		}
		public static ResourceSet operator *(uint scalar, ResourceSet rs2) {
			return rs2 * scalar;
		}
		public void Set(String name, String value) {
			switch (name) {
				case "Eisen":
					this.Eisen = float.Parse(value);
					break;
				case "Stahl":
				case "Produktionskapazität für Stahl":
					this.Stahl = float.Parse(value);
					break;
				case "Wasser":
				case "Produktionskapazität für Wasser":
					this.Wasser = float.Parse(value);
					break;
				case "Energie":
					this.Energie = float.Parse(value);
					break;
				case "chem. Elemente":
					this.Chemie = float.Parse(value);
					break;
				case "Bevölkerung":
					this.Bev = float.Parse(value);
					break;
				case "Eis":
					this.Eis = float.Parse(value);
					break;
				case "Credits":
				case "Zufriedenheit":
					this.Credits = float.Parse(value);
					break;
				case "VV4A":
				case "Produktionskapazität für VV4A":
					this.VV4A = float.Parse(value);
					break;
				case "Forschungspunkte":
					this.FP = float.Parse(value);
					break;
				default:
					Sonstiges = Sonstiges + value + " " + name + ", ";
					break;
			}
		}
		public void Set(int num, String value) {
			float val = float.Parse(value);
			switch(num) {
				case 0:
					Eisen = val;
					break;
				case 1:
					Stahl = val;
					break;
				case 2:
					VV4A = val;
					break;
				case 3:
					Chemie = val;
					break;
				case 4:
					Eis = val;
					break;
				case 5:
					Wasser = val;
					break;
				case 6:
					Energie = val;
					break;
			}
		}
        public void SetIWID(int num, String value) {
			float val = float.Parse(value);
            switch (num) {
                case 1:
                    Eisen = val;
                    break;
                case 2:
                    Stahl = val;
                    break;
                case 3:
                    VV4A = val;
                    break;
                case 5:
                    Chemie = val;
                    break;
                case 4:
                    Eis = val;
                    break;
                case 6:
                    Wasser = val;
                    break;
                case 7:
                    Energie = val;
                    break;
            }
        }
		public void ParseXml(XmlNode ressourcenXml) {
			foreach (XmlNode n in ressourcenXml.SelectNodes("ressource")) {
				int id = int.Parse(n.SelectSingleNode("id").InnerText);
				SetIWID(id, n.SelectSingleNode("anzahl").InnerText);
			}
		}
		public void ParseXmlKb(XmlNode ressourcenXml) {
			foreach(XmlNode n in ressourcenXml.SelectNodes("resource")) {
				int id = int.Parse(n.SelectSingleNode("id").Attributes["value"].InnerText);
				SetIWID(id, n.SelectSingleNode("anzahl").Attributes["value"].InnerText);
			}
		}
		public float RaidScore { get { return Eisen * 1 + Stahl * 2 + Chemie * 1.5f + VV4A * 4 + Eis * 2 + Wasser * 4 + Energie; } }
	}
	class TechtreeItemStufe {
		public int Stufe;
		public int Dauer;
		public ResourceSet BauKosten;
		public ResourceSet laufendeEffekte;

		public TechtreeItemStufe(String kostenZeileToParse) {
			BauKosten = new ResourceSet();
			laufendeEffekte = new ResourceSet();
			Match levelMatch = Regex.Match(kostenZeileToParse, @"(?:Stufe|globale Anzahl) (\d+):\s+(.*)");
			if (levelMatch.Success) {
				Stufe = int.Parse(levelMatch.Groups[1].Value);
				kostenZeileToParse = levelMatch.Groups[2].Value;
			} else {
				Stufe = 1;
				Match fpMatch = Regex.Match(kostenZeileToParse, "("+IWDBRegex.Number+@") Forschungspunkte(:?\n(.*))?");
				if (fpMatch.Success) {
					BauKosten.Set("Forschungspunkte", fpMatch.Groups[1].Value);
					kostenZeileToParse = fpMatch.Groups[2].Value;
				}
			}
			
			MatchCollection matches = Regex.Matches(kostenZeileToParse, @"(\S.+?):\s+(\d+)");
			foreach (Match m in matches) {
				BauKosten.Set(m.Groups[1].Value, m.Groups[2].Value);
			}
		}

		public void ParseDauer(String dauer) {
			if (Stufe != 0) {
				Match m = Regex.Match(dauer, "(?:Stufe|globale Anzahl) " + Stufe + @":\s+(" + IWDBRegex.IWZeitspanne + ")");
				if(m.Success) {
					dauer = m.Groups[1].Value;
				}
			}
			String[] parts = dauer.Split(new string[] { " Tage ", " Tag " }, 2, StringSplitOptions.None);
			if (parts.Length > 1) {
				Dauer = 86400*int.Parse(parts[0]);
				dauer = parts[1];
			}
			parts = dauer.Split(':');
			Dauer = Dauer + 3600 * int.Parse(parts[0]);
			Dauer = Dauer + 60 * int.Parse(parts[1]);
			Dauer = Dauer + int.Parse(parts[2]);
		}

		public void ParseBringt(String bringt) {
			if (Stufe != 0) {
				Match m = Regex.Match(bringt, "(?:Stufe|globale Anzahl) " + Stufe + @":\s+(.*)\n");
				if (m.Success) {
					bringt = m.Groups[1].Value;
				}
			}
			MatchCollection matches = Regex.Matches(bringt, @"([+-][\d,.]+)\s+(.+?)(?:,\s+|$)");
			foreach (Match m in matches) {
				laufendeEffekte.Set(m.Groups[2].Value, m.Groups[1].Value);
			}
		}

		internal void ParseLaufendeKosten(string laufendeKosten) {
			ParseBringt(laufendeKosten);
		}
	}
	abstract class Gebäude:TechtreeItem {
		protected Gebäude(String DBPrefix) : base("geb", DBPrefix) { }
		public static Gebäude Parse(String gebInfo, String DBPrefix) {
			return NormGeb.ParseNormGeb(gebInfo, DBPrefix);
		}
	}
	class NormGeb : Gebäude {
		public static NormGeb ParseNormGeb(String gebInfo, String DBPrefix) {
			Match m = Regex.Match(gebInfo, @"Gebäudeinfo:\s+(.+)\n
	([\s\S]*?)\n
	(?:Stufengebäude\n
	[\s\S]+?\n
	Stufe\s+(\d+)\n)?
	Kosten\s+([\s\S]+?)\n
	Dauer\s+(" + IWDBRegex.IWZeitspanne + @"|[\s\S]+?)\n
	bringt\s+([\s\S]+?)\n
	(?:Kosten\s+(.*)\n)?
	(?:Maximale\sAnzahl\s+(\d+)\n)?
	Voraussetzungen\sForschungen\s+?((?:\(.*\))*)\n
	Voraussetzungen\sGebäude\s+?((?:\(.*\))*)\n
	(?:Voraussetzungen\sPlanetentyp\s+?(.*)\n)?
	(?:Voraussetzungen\sKolonietyp\s+?(.*)\n)?
	Kann\szerstört\swerden\sdurch\s+?([\s\S]*?)\n
	Ermöglicht\sForschungen\s+?((?:\(.*\))*)\n
	Ermöglicht\sGebäude\s+?((?:\(.*\))*)\n
	Farbenlegende", RegexOptions.IgnorePatternWhitespace);
			if (!m.Success)
				return null;
			NormGeb geb = new NormGeb(m, DBPrefix);
			return geb;
		}

		private NormGeb(Match m, String DBPrefix)
			: base(DBPrefix) {
			
			this.name = m.Groups[1].Value;
			this.beschreibung = m.Groups[2].Value;
			String[] kostenZeilen = m.Groups[4].Value.Split(new char[] { '\n' }, StringSplitOptions.RemoveEmptyEntries);
			if (kostenZeilen.Length == 1) {
				cls = ItemClass.Normal;
			} else {
				cls = ItemClass.UnechtesStufengeb;
			}
			foreach (String kosten in kostenZeilen) {
				TechtreeItemStufe stufe = new TechtreeItemStufe(kosten);
				stufe.ParseDauer(m.Groups[5].Value);
				stufe.ParseBringt(m.Groups[6].Value);
				stufe.ParseLaufendeKosten(m.Groups[7].Value);
				if (m.Groups[3].Success) {
					cls = ItemClass.Stufengeb;
					stufe.Stufe = int.Parse(m.Groups[3].Value);
				}
				this.stufen.Add(stufe.Stufe, stufe);
			}

			if (m.Groups[8].Success) {
				this.maxLevel = int.Parse(m.Groups[8].Value);
			}

			BedingungsParser(m.Groups[9].Value, benForschungen);
			BedingungsParser(m.Groups[10].Value, benGebs);
			benPlanityp = m.Groups[11].Value;
			benKolotyp = m.Groups[12].Value;
			zerstörungDurch.AddRange(m.Groups[13].Value.Split(new char[] { '\n' }, StringSplitOptions.RemoveEmptyEntries));
			BedingungsParser(m.Groups[14].Value, ermForschungen);
			BedingungsParser(m.Groups[15].Value, ermGebs);
		}

	}
	class Forschung : TechtreeItem {
		public static Forschung Parse(String forschungsInfo, String DBPrefix) {
			Match m = Regex.Match(forschungsInfo, @"Forschungsinfo:\s+(.+?)\n
	Status\s+(.+?)\n
	Gebiet\s+(.+?)\n
	(?:(.*?)\n)?
	(?:Zuerst\serforscht\svon\s+(.+?)\n)?
	Kosten\s+("+IWDBRegex.Number+ @"\s+Forschungspunkte(?:\n.+?)?)\n
	(?:\s*Aufgrund\svon\sgenerellen\stechnischen\sUnverständnis\sim\sUniversum,\sliegen\sdie\sForschungskosten\sbei\s\d+\s%\.\n)?
	(?:\s*\(von\s\d+%\sLeuten\serforscht,\s\d+%\sFPKosten\)\n)?
	(?:\s*Prototyp.+?\n)?
	\s*Voraussetzungen\sForschungen\s+?((?:\(.*\))*)\n
	Voraussetzungen\sGebäude\s+?((?:\(.*\))*)\n
	Voraussetzungen\sObjekte\s+(.*?)\n
	Ermöglicht\sForschungen\s+?((?:\(.*\))*)\n
	Ermöglicht\sGebäude\s+?((?:\(.*\))*)\n
	Ermöglicht\sGebäudestufen\s+?((?:\(.*\))*)\n
	Ermöglicht\sVerteidigungsanlagen\s+?((?:\(.*\))*)\n
	Ermöglicht\sGenetikoptionen\s+?((?:\(.*\))*)\n
	Farbenlegende", RegexOptions.IgnorePatternWhitespace);
			if (!m.Success)
				return null;
			Forschung f = new Forschung(m, DBPrefix);
			return f;
		}


		protected Forschung(Match m, String DBPrefix) : base("for", DBPrefix) {
			this.maxLevel = 1;
			this.name = m.Groups[1].Value;
			this.gebiet = m.Groups[3].Value;
			this.beschreibung = m.Groups[4].Value;
			TechtreeItemStufe stufe = new TechtreeItemStufe(m.Groups[6].Value);
			this.stufen.Add(stufe.Stufe, stufe);
			BedingungsParser(m.Groups[7].Value, benForschungen);
			BedingungsParser(m.Groups[8].Value, benGebs);
			BedingungsParser(m.Groups[10].Value, ermForschungen);
			BedingungsParser(m.Groups[11].Value, ermGebs);
			List<String> ermStufen = new List<string>();
			BedingungsParser(m.Groups[12].Value, ermStufen);
			foreach (String str in ermStufen) {
				String[] parts = str.Split(new string[] { " Stufe " }, StringSplitOptions.None);
				ermGebs.Remove(parts[0]);
			}
			ermGebs.AddRange(ermStufen);
			BedingungsParser(m.Groups[13].Value, ermDef);
		}
	}
	class Schiff : TechtreeItem {
		public Schiff(Match m, String DBPrefix)
			: base("schiff", DBPrefix) {
			this.name = m.Groups[1].Value;
			TechtreeItemStufe stufe = new TechtreeItemStufe(m.Groups[2].Value);
			stufe.ParseDauer(m.Groups[3].Value);
			this.stufen.Add(stufe.Stufe, stufe);
		}
	}
	class SchiffsKostenXmlParser : ReportParser {
		public SchiffsKostenXmlParser(NewscanHandler h)
			: base(h) {
				AddPatern("http://www.icewars.de/portal/xml/de/schiffkosten.xml", PatternFlags.All);
		}
		public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			String str = IWCache.WebQuery(matches[0].Value);
			System.Xml.XmlDocument d = new System.Xml.XmlDocument();
			d.LoadXml(str);
			foreach(XmlNode sch in d["struktur"].SelectNodes("schiff")) {
				MySqlCommand insertItem = new MySqlCommand("INSERT IGNORE INTO " + DBPrefix + "techtree_items (name, type) VALUES (?name, 'schiff')", con);
				insertItem.Parameters.Add("?name", MySqlDbType.String).Value = sch["name"].InnerText;
				insertItem.ExecuteNonQuery();
				long itemID = insertItem.LastInsertedId;
				if(itemID == 0) {
					MySqlCommand sel = new MySqlCommand("SELECT ID FROM " + DBPrefix + "techtree_items WHERE name=?name AND type='schiff'", con);
					sel.Parameters.Add("?name", MySqlDbType.String).Value = sch["name"].InnerText;
					Object obj = sel.ExecuteScalar();
					itemID = (uint)obj;
				}
				ResourceSet kosten = new ResourceSet();
				kosten.ParseXml(sch["kosten"]);
				kosten.Zeit = TimeSpan.FromSeconds(int.Parse(sch["dauer"].InnerText));
				MySqlCommand insertStufe = new MySqlCommand("INSERT INTO " + DBPrefix + @"techtree_stufen (ItemID, Stufe, Dauer, bauE,bauS,bauC,bauV,bauEis,bauW,bauEn,bauCr,bauBev) VALUES (?itemid, 1, ?dauer, ?baue, ?baus, ?bauc, ?bauv, ?baueis, ?bauw, ?bauen, ?baucr, ?baubev)
ON DUPLICATE KEY UPDATE Dauer=VALUES(Dauer), bauE=VALUES(bauE), bauS=VALUES(bauS), bauC=VALUES(bauC), bauV=VALUES(bauV), bauEis=VALUES(bauEis), bauW=VALUES(bauW), bauEn=VALUES(bauEn), bauCr=VALUES(bauCr), bauBev=VALUES(bauBev)", con);
				insertStufe.Parameters.Add("?itemid", MySqlDbType.UInt32).Value = itemID;
				insertStufe.Parameters.Add("?dauer", MySqlDbType.UInt32).Value = kosten.Zeit.TotalSeconds;
				insertStufe.Parameters.Add("?baue", MySqlDbType.UInt32).Value = kosten.Eisen;
				insertStufe.Parameters.Add("?baus", MySqlDbType.UInt32).Value = kosten.Stahl;
				insertStufe.Parameters.Add("?bauc", MySqlDbType.UInt32).Value = kosten.Chemie;
				insertStufe.Parameters.Add("?bauv", MySqlDbType.UInt32).Value = kosten.VV4A;
				insertStufe.Parameters.Add("?baueis", MySqlDbType.UInt32).Value = kosten.Eis;
				insertStufe.Parameters.Add("?bauw", MySqlDbType.UInt32).Value = kosten.Wasser;
				insertStufe.Parameters.Add("?bauen", MySqlDbType.UInt32).Value = kosten.Energie;
				insertStufe.Parameters.Add("?baucr", MySqlDbType.UInt32).Value = kosten.Credits;
				insertStufe.Parameters.Add("?baubev", MySqlDbType.UInt32).Value = kosten.Bev;
				insertStufe.ExecuteNonQuery();
			}
			resp.Respond("Schiffskosten-XML erfolgreich eingelesen!");
		}
	}

}
