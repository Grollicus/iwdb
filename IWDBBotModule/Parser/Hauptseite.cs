using System;
using System.Linq;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;
using System.Collections.Generic;
using System.Text;
using System.Threading;
namespace IWDB.Parser {
	class HauptseiteKolonieinformationParser : ReportParser { //Beachte: Dieser Parser muss der ERSTE Hauptseitenparser sein!
		Dictionary<uint, OrderedList<FlottenCacheFlotte>> flottenCache;
		List<uint> ownerCache;
        Dictionary<uint, uint> oldLastParsed;
		WarFilter warFilter;
		public HauptseiteKolonieinformationParser(NewscanHandler newscanHandler, WarFilter warFilter)
			: base(newscanHandler, false) {
            AddPattern(@"Kolonieinformation\s+(?:" + IWObjektTyp + @")\s+" + KolonieName + @"\s+" + KoordinatenMatch + @"[\s\S]+?
			#Lebensbedingungen,Flottenscannerreichweite, Leerzeile danach, Serverzeit, Kolonien aktuell/maximal, Schiffsübersicht,...
			Forschungsstatus\s+
			([^\n]+)\s+("+IWZeit+")");
			AddPattern(@"Kolonieinformation\s+(?:" + IWObjektTyp + @")\s+" + KolonieName + @"\s+" + KoordinatenMatch);
			flottenCache = RequestCache<Dictionary<uint, OrderedList<FlottenCacheFlotte>>>("FlottenCache");
			ownerCache = RequestCache<List<uint>>("OwnerCache");
            oldLastParsed = RequestCache<Dictionary<uint, uint>>("OldLastParsed");
			this.warFilter = warFilter;
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

		public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			ownerCache.Clear();
			foreach(Match m in matches) {
				PlaniFetcher f = new PlaniFetcher(handler.BesData, con, DBPrefix);
				String[] parts = m.Groups[1].Value.Split(':');
                if (parts.Length < 3) {
                    resp.RespondError("Fehler beim Einlesen der Koloinformationen (" + m.Groups[1].Value + "): Koordinaten kaputt!\n");
                    return;
                }
				f.Gala = uint.Parse(parts[0]);
				f.Sys = uint.Parse(parts[1]);
				f.Pla = uint.Parse(parts[2]);
				List<PlaniInfo> infos = f.FetchMatching(PlaniDataFlags.Ownername | PlaniDataFlags.IgmID);
				if(infos.Count < 1) {
					resp.RespondError("Fehler beim Einlesen der Koloinformationen (" + m.Groups[1].Value + "): Unidaten nicht aktuell oder der Spieler hat noch keine Daten eingetragen!\n");
					return;//öh ja, Fehlerbehandlung?^^
				}
				uint uid = infos[0].IgmUserID;
				String planiowner = infos[0].Ownername;
				ownerCache.Add(uid);

				//Hier wird auch das Cleanup für alle anderne Hauptseitenelemente vorgenommen.
				if(!flottenCache.ContainsKey(uid)) {
					MySqlCommand flottenSelect = new MySqlCommand(@"SELECT ID, startid, zielid, action, ankunft, nummer, erinnerungsstatus FROM " + DBPrefix + "flotten WHERE zielid IN (SELECT universum.ID FROM " + DBPrefix + "universum AS universum WHERE ownername=?owner)", con);
					flottenSelect.Parameters.Add("?owner", MySqlDbType.String).Value = planiowner;
					MySqlDataReader r = flottenSelect.ExecuteReader();
					OrderedList<FlottenCacheFlotte> flottenData = new OrderedList<FlottenCacheFlotte>(new FlottenComparer());
					while(r.Read()) {
						FlottenCacheFlotte flotte = new FlottenCacheFlotte(r.GetUInt32(0), r.GetUInt32(1), r.GetUInt32(2), r.GetUInt32(4), r.GetUInt32(5), r.GetUInt32(6), r.GetString(3));
						flottenData.Add(flotte);
					}
					r.Close();
					flottenCache.Add(uid, flottenData);
					handler.ActivatePostHandler("flottenCleanup");
				}
				MySqlCommand bauCleanup = new MySqlCommand(@"DELETE FROM " + DBPrefix + "building WHERE uid=?uid", con);
				bauCleanup.Parameters.Add("?uid", MySqlDbType.UInt32).Value = uid;
				bauCleanup.Prepare();
				bauCleanup.ExecuteNonQuery();

				if(m.Groups[2].Success) {
					MySqlCommand iwsaQry = new MySqlCommand("SELECT iwsa FROM " + DBPrefix + "igm_data WHERE ID=?id", con);
					iwsaQry.Parameters.Add("?id", MySqlDbType.UInt32).Value = uid;
					iwsaQry.Prepare();
					bool iwsa = (bool)iwsaQry.ExecuteScalar();

					MySqlCommand qry = new MySqlCommand("INSERT INTO " + DBPrefix + "building (uid, plani, end) VALUES (?uid, 0, ?end)", con);
					qry.Parameters.Add("?uid", MySqlDbType.UInt32).Value = uid;
					if(iwsa)
						qry.Parameters.Add("?end", MySqlDbType.UInt32).Value = IWDBUtils.parseIWTime(m.Groups[3].Value) + 10800;
					else
						qry.Parameters.Add("?end", MySqlDbType.UInt32).Value = IWDBUtils.parseIWTime(m.Groups[3].Value);
					qry.Prepare();
					qry.ExecuteNonQuery();
				}

				//lastParse
				String bonus = "";
				uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);
				MySqlCommand lastParsedQry = new MySqlCommand("SELECT lastParsed FROM " + DBPrefix + "igm_data WHERE ID=?id", con);
				lastParsedQry.Parameters.Add("?id", MySqlDbType.UInt32).Value = uid;
				Object ret = lastParsedQry.ExecuteScalar();
				if(ret == null) {
					resp.RespondError("Hurr? Ganz seltsamer Fehler dass der geparste Account mir nicht bekannt ist ?! :o");
					return;
				}
				uint lastUpdTime = (uint)ret;
                oldLastParsed[uid] = lastUpdTime;
                if (m.Groups[2].Success) {
                    MySqlCommand lastParseUpd = new MySqlCommand(@"UPDATE " + DBPrefix + "igm_data SET lastParsed=?lp, forschung=?for, forschung_ende=?end WHERE ID=?id", con);
                    lastParseUpd.Parameters.Add("?lp", MySqlDbType.UInt32).Value = now;
                    lastParseUpd.Parameters.Add("?for", MySqlDbType.UInt32).Value = getItemID(m.Groups[2].Value, "geb", con);
                    lastParseUpd.Parameters.Add("?end", MySqlDbType.UInt32).Value = IWDBUtils.parseIWTime(m.Groups[3].Value);
                    lastParseUpd.Parameters.Add("?id", MySqlDbType.UInt32).Value = uid;
                    lastParseUpd.ExecuteNonQuery();
                } else {
                    MySqlCommand lastParseUpd = new MySqlCommand(@"UPDATE " + DBPrefix + "igm_data SET lastParsed=?lp WHERE ID=?id", con);
                    lastParseUpd.Parameters.Add("?lp", MySqlDbType.UInt32).Value = now;
                    lastParseUpd.Parameters.Add("?id", MySqlDbType.UInt32).Value = uid;
                    lastParseUpd.ExecuteNonQuery();
                }

				int sitterfact = 1;
				if(warFilter.InWar) {
					sitterfact = 5;
					uint schedule_slot = now - (now % 1800);
					MySqlCommand sitterSlotQry = new MySqlCommand("SELECT count(*) FROM "+DBPrefix+"war_schedule WHERE time=?time AND userid=?uid", con);
					sitterSlotQry.Parameters.Add("?time", MySqlDbType.UInt32).Value = schedule_slot;
					sitterSlotQry.Parameters.Add("?uid", MySqlDbType.UInt32).Value=posterID;
					ret = sitterSlotQry.ExecuteScalar();
					if(ret != null && !Convert.IsDBNull(ret) && Convert.ToUInt32(ret) > 0) {
						sitterfact = 20;
						bonus = "[+Sitter]";
					} else {
						bonus = "[+Krieg]";
					}
				}
                MySqlCommand sitterScoreUpd = new MySqlCommand(@"UPDATE " + DBPrefix + "users SET sittertime=sittertime+?add WHERE ID=?uid", con);
				sitterScoreUpd.Parameters.Add("?add", MySqlDbType.UInt32).Value = (now - lastUpdTime) * sitterfact;
				sitterScoreUpd.Parameters.Add("?uid", MySqlDbType.UInt32).Value = posterID;
                sitterScoreUpd.ExecuteNonQuery();
				resp.Respond("Kolonieinformationen (" + planiowner + ") eingelesen! "+bonus+"\n");
			}
		}
	}

    class HauptseiteAusbaustatusParser : ReportParser {
		public HauptseiteAusbaustatusParser(NewscanHandler newscanHandler)
			: base(newscanHandler, false) {
            AddPattern(@"Ausbaustatus
		((?:\n" + KolonieName + @"\s+" + Koordinaten + @"\s+(?:(?:nÜscht[^\n])|(?:.*?\s+bis\s+" + IWZeit + @"[\s\-]+" + IWZeitspanne + ")))+)");
        }
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			foreach (Match outerMatch in matches) {
				MatchCollection c = Regex.Matches(outerMatch.Groups[1].Value, "\n" + KolonieName + @"\s+" + KoordinatenEinzelMatch + @"\s+(?:(?:nÜscht)|(?:.*bis\s+(" + IWZeit + @")(?:\n|\s-).*))");
				foreach (Match m in c) {
					try {
						int gala = int.Parse(m.Groups[1].Value);
						int sys = int.Parse(m.Groups[2].Value);
						int pla = int.Parse(m.Groups[3].Value);

						MySqlCommand uidAndplanIDQuery = new MySqlCommand("SELECT igm_data.id, universum.ID FROM (" + DBPrefix + "universum AS universum) INNER JOIN (" + DBPrefix + @"igm_data AS igm_data)
					ON universum.ownername = igm_data.igmname WHERE universum.gala=?gala AND universum.sys=?sys AND universum.pla=?pla", con);
						uidAndplanIDQuery.Parameters.Add("?gala", MySqlDbType.Int32).Value = gala;
						uidAndplanIDQuery.Parameters.Add("?sys", MySqlDbType.Int32).Value = sys;
						uidAndplanIDQuery.Parameters.Add("?pla", MySqlDbType.Int32).Value = pla;
						uidAndplanIDQuery.Prepare();
						MySqlDataReader reader = uidAndplanIDQuery.ExecuteReader();
						if (!reader.Read()) {
							reader.Close();
							resp.RespondError("Fehler beim Einlesen des Ausbaustatus (" + m.Groups[1].Value + ":" + m.Groups[2].Value + ":" + m.Groups[3].Value + "): Unidaten nicht aktuell oder der Spieler hat noch keine Daten eingetragen!\n");
							continue;//öh ja, Fehlerbehandlung?^^
						}
						uint uid = reader.GetUInt32(0);
						uint planID = reader.GetUInt32(1);
						reader.Close();
						try {
							MySqlCommand cmd = new MySqlCommand("INSERT INTO " + DBPrefix + "building (uid, plani, end) VALUES (?uid, ?plani, ?end)", con);
							cmd.Parameters.Add("?uid", MySqlDbType.UInt32).Value = uid;
							cmd.Parameters.Add("?plani", MySqlDbType.UInt32).Value = planID;
							cmd.Parameters.Add("?end", MySqlDbType.Int32).Value = m.Groups[4].Success ? IWDBUtils.parseIWTime(m.Groups[4].Value) : 0;
							cmd.Prepare();
							cmd.ExecuteNonQuery();
						} catch (NullReferenceException e) {
							IRCeX.Log.WriteLine(IRCeX.LogLevel.E_ERROR, "NRE in Part2");
							throw new Exception("NRE in Part2!", e);
						}
					} catch (NullReferenceException e) {
						IRCeX.Log.WriteLine(IRCeX.LogLevel.E_ERROR, "NRE in Part1");
						throw new Exception("NRE in Part1!", e);
					}
				}
				resp.Respond("Ausbaustatus eingelesen!\n");
			}
		}
	}
	class HauptseiteFremdeFlottenParser:ReportParser {
		const String Aktionen = @"Sondierung\s\(Gebäude/Ress\)|Sondierung\s\(Geologie\)|Sondierung\s\(Schiffe/Def/Ress\)|Transport|Übergabe|Ressourcenhandel\s\(ok\)|Ressourcenhandel";
		Dictionary<uint, OrderedList<FlottenCacheFlotte>> flottenCache;
		List<uint> ownerCache;
		IWDBParser parser;
        Dictionary<uint, uint> oldLastParsed;
		public HauptseiteFremdeFlottenParser(NewscanHandler h, IWDBParser parser)
			: base(h, false) {
            AddPattern(@"fremde\sFlotten\s+
Fremde\sFlotten\n
(?:\(Es\ssind\sfremde\sFlotten\süber\sdem\sPlaneten\sstationiert\.\)\s+)?
Ziel\s+Start\s+Ankunft\s+Aktionen\s+\+
((?:\s*\n" + KolonieName + @"\s" + Koordinaten + @"\s+" + KolonieName + @"\s" + Koordinaten + @"\n
" + SpielerName + @"\s+(?:" + PräziseIWZeit + @"|" + AbladeAktionen + @")[\s\-]+(?:[\d:]+|angekommen)\s+(?:" + Aktionen + @")(?:[\s\S]+?\+)?)+)", PatternFlags.All);
			flottenCache = RequestCache<Dictionary<uint, OrderedList<FlottenCacheFlotte>>>("FlottenCache");
			ownerCache = RequestCache<List<uint>>("OwnerCache");
            oldLastParsed = RequestCache<Dictionary<uint, uint>>("OldLastParsed");
			this.parser = parser;
		}
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			PlaniIDFetcher idFetcherStartID = new PlaniIDFetcher(KnownData.Name|KnownData.Owner, con, DBPrefix);
			PlaniIDFetcher idFetcherZielID = new PlaniIDFetcher(KnownData.Name | KnownData.Owner, con, DBPrefix);

			MySqlCommand uidQuery = new MySqlCommand("SELECT igm_data.id, igm_data.igmname FROM (" + DBPrefix + "universum AS universum) INNER JOIN (" + DBPrefix + @"igm_data AS igm_data)
						ON universum.ownername = igm_data.igmname WHERE universum.gala=?gala AND universum.sys=?sys AND universum.pla=?pla", con);
			uidQuery.Parameters.Add("?gala", MySqlDbType.Int32);
			uidQuery.Parameters.Add("?sys", MySqlDbType.Int32);
			uidQuery.Parameters.Add("?pla", MySqlDbType.Int32);
			uidQuery.Prepare();

            MySqlCommand insertQry = new MySqlCommand(@"INSERT INTO " + DBPrefix + @"flotten (startid, zielid, action, ankunft, nummer, firstseen, notyetseen) VALUES (?start, ?ziel, ?action, ?ankunft, ?nummer, ?firstseen, ?notyetseen)", con);
			insertQry.Parameters.Add("?start", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?ziel", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?action", MySqlDbType.Enum);
			insertQry.Parameters.Add("?ankunft", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?nummer", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?firstseen", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?notyetseen", MySqlDbType.UInt32);
			insertQry.Prepare();

			MySqlCommand deleteQry = new MySqlCommand(@"DELETE FROM " + DBPrefix + @"flotten WHERE id=?id", con);
			deleteQry.Parameters.Add("?id", MySqlDbType.UInt32);
			deleteQry.Prepare();

            uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);

			foreach(Match outerMatch in matches) {
				MatchCollection innerMatches = Regex.Matches(outerMatch.Groups[0].Value, "(" + KolonieName + @")\s" + KoordinatenEinzelMatch + @"\s+(" + KolonieName + @")\s" + KoordinatenEinzelMatch + @"\n
(" + SpielerName + @")\s+(" + PräziseIWZeit + @"|" + AbladeAktionen + @")[\s\-]+
(?:[\d:]+|angekommen)\s+(" + Aktionen + @")", RegexOptions.IgnorePatternWhitespace);
				if(innerMatches.Count == 0) {
					resp.RespondError("Hab hier fremde Flotten ohne Flotten! Evtl läuft da was schief.. bitte mal melden!");
					continue;
				}
				uint uid = 0;
				Dictionary<uint, Pair<string, uint>> ownerCount = new Dictionary<uint, Pair<string, uint>>();
				for(int i = 0; i < innerMatches.Count; ++i) {
					uidQuery.Parameters["?gala"].Value = int.Parse(innerMatches[i].Groups[2].Value);
					uidQuery.Parameters["?sys"].Value = int.Parse(innerMatches[i].Groups[3].Value);
					uidQuery.Parameters["?pla"].Value = int.Parse(innerMatches[i].Groups[4].Value);
					MySqlDataReader r = uidQuery.ExecuteReader();
					try {
						if(!r.Read())
							continue;
						uid = (uint)r.GetUInt32(0);
						if(ownerCount.ContainsKey(uid))
							ownerCount[uid].Item2++;
						else
							ownerCount.Add(uid, new Pair<string, uint>(r.GetString(1), 1));
					} finally {
						r.Close();
					}
				}
				Tuple<uint, string, uint> ownerData = ownerCache.Select(key => { Pair<string, uint> val; return ownerCount.TryGetValue(key, out val) ? new Tuple<uint, string, uint>(key, val.Item1, val.Item2) : new Tuple<uint, string, uint>(key, "", 0); }).Aggregate(new Tuple<uint, string, uint>(0, "", 0), (p1, p2) => p1.Item3 >= p2.Item3 ? p1 : p2); //heres to hopin noone ever has to understand that
				uid = ownerData.Item1;
				if(uid == 0 || ownerData.Item3 == 0) {
					resp.RespondError("Mir ist kein einziger der von fremden Flotten angeflogenen Zielplanis (" + innerMatches[0].Groups[2].Value + ":" + innerMatches[0].Groups[3].Value + ":" + innerMatches[0].Groups[4].Value + ") bekannt: Die gesamten fremden Flotten wurden übersprungen!\n");
					continue;
				}

				OrderedList<FlottenCacheFlotte> flotten = new OrderedList<FlottenCacheFlotte>(new FlottenComparer());
				Dictionary<uint, uint> arrivalsAtTime = new Dictionary<uint, uint>();

				foreach(Match m in innerMatches) {
					uint zielID = idFetcherZielID.GetID(uint.Parse(m.Groups[2].Value), uint.Parse(m.Groups[3].Value), uint.Parse(m.Groups[4].Value), m.Groups[1].Value, ownerData.Item2);
					uint startID = idFetcherStartID.GetID(uint.Parse(m.Groups[6].Value), uint.Parse(m.Groups[7].Value), uint.Parse(m.Groups[8].Value), m.Groups[5].Value, m.Groups[9].Value);
					uint time = IWDBUtils.parsePreciseIWTime(m.Groups[10].Value);
					uint num = arrivalsAtTime.ContainsKey(time) ? ++arrivalsAtTime[time] : (arrivalsAtTime[time] = 0);

					FlottenCacheFlotte flotte = new FlottenCacheFlotte(0, startID, zielID, time, num, 0, m.Groups[11].Value);
					flotten.Add(flotte);
				}
				OrderedList<FlottenCacheFlotte> cachedFlotten;
				if(!flottenCache.TryGetValue(uid, out cachedFlotten)) {
					cachedFlotten = new OrderedList<FlottenCacheFlotte>(new FlottenComparer());
				}
				List<OrderedListDifference<FlottenCacheFlotte>> diffs = cachedFlotten.Differences(flotten);
				int neu = 0;
				String ziel = "";
				foreach(OrderedListDifference<FlottenCacheFlotte> diff in diffs) {
					if(diff.Item.Action == "Angriff")
						continue;
					if(diff.Difference == OrderedListDifferenceType.MissingInCompared) {
						deleteQry.Parameters["?id"].Value = diff.Item.id;
						deleteQry.ExecuteNonQuery();
					} else {
						insertQry.Parameters["?start"].Value = diff.Item.startid;
						insertQry.Parameters["?ziel"].Value = diff.Item.zielid;
						insertQry.Parameters["?action"].Value = diff.Item.Action;
						insertQry.Parameters["?ankunft"].Value = diff.Item.ankunft;
						insertQry.Parameters["?nummer"].Value = diff.Item.nummer;
                        insertQry.Parameters["?firstseen"].Value = now;
                        insertQry.Parameters["?notyetseen"].Value = oldLastParsed.GetOrDefault(uid, (uint)0);
						insertQry.ExecuteNonQuery();
						if(neu == 0) {
							MySqlCommand zielQry = new MySqlCommand("SELECT ownername FROM " + DBPrefix + "universum WHERE id=?id", con);
							zielQry.Parameters.Add("?id", MySqlDbType.UInt32).Value = diff.Item.zielid;
							ziel = zielQry.ExecuteScalar() as String;
						}
						if(diff.Item.Action == "Sondierung (Gebäude/Ress)" || diff.Item.Action == "Sondierung (Geologie)" || diff.Item.Action == "Sondierung (Schiffe/Def/Ress)")
							++neu;
					}
				}
				if(neu > 0)
					parser.NeueFlottenGesichtet(ziel, neu, false);
				cachedFlotten.RemoveMatching(delegate(FlottenCacheFlotte f) { return f.Action != "Angriff"; });
				resp.Respond("Fremde Flotten (" + ownerData.Item2 + ") eingelesen!\n");
			}
		}
	}
	class HauptseiteFeindlicheFlottenParser:ReportParser {
		Dictionary<uint, OrderedList<FlottenCacheFlotte>> flottenCache;
        Dictionary<uint, uint> oldLastParsed;
		List<uint> ownerCache;
        IWDBParser parser;
        public HauptseiteFeindlicheFlottenParser(NewscanHandler h, IWDBParser parser)
			: base(h, false) {
			AddPattern(@"feindliche\sFlotten\s+
Fremde\sFlotten\n
Ziel\s+Start\s+Ankunft\s+Aktionen\s+
((?:\s*\n(?:" + KolonieName + @"\s|)" + Koordinaten + @"\s+" + KolonieName + @"\s" + Koordinaten + @"\n
" + SpielerName + @"\s+(?:" + PräziseIWZeit + @"|Plündert,\sMordet\sund\sBrandschatzt)[\s\-]+.*?\s+Angriff)+)", PatternFlags.All);
			flottenCache = RequestCache<Dictionary<uint, OrderedList<FlottenCacheFlotte>>>("FlottenCache");
			ownerCache = RequestCache<List<uint>>("OwnerCache");
            oldLastParsed = RequestCache<Dictionary<uint, uint>>("OldLastParsed");
			this.parser = parser;
		}
        public override void Matched(MatchCollection matches, uint posterID, uint victimID, MySqlConnection con, SingleNewscanRequestHandler handler, ParserResponse resp) {
			PlaniIDFetcher idFetcherStartID = new PlaniIDFetcher(KnownData.Name | KnownData.Owner, con, DBPrefix);
			PlaniIDFetcher idFetcherZielID = new PlaniIDFetcher(KnownData.Name | KnownData.Owner, con, DBPrefix);

			MySqlCommand uidQuery = new MySqlCommand("SELECT igm_data.id, igm_data.igmname FROM (" + DBPrefix + "universum AS universum) INNER JOIN (" + DBPrefix + @"igm_data AS igm_data)
						ON universum.ownername = igm_data.igmname WHERE universum.gala=?gala AND universum.sys=?sys AND universum.pla=?pla", con);
			uidQuery.Parameters.Add("?gala", MySqlDbType.Int32);
			uidQuery.Parameters.Add("?sys", MySqlDbType.Int32);
			uidQuery.Parameters.Add("?pla", MySqlDbType.Int32);
			uidQuery.Prepare();
            MySqlCommand insertQry = new MySqlCommand(@"INSERT INTO " + DBPrefix + @"flotten (startid, zielid, action, ankunft, nummer, firstseen, notyetseen) VALUES (?start, ?ziel, ?action, ?ankunft, ?nummer, ?firstseen, ?notyetseen)", con);
			insertQry.Parameters.Add("?start", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?ziel", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?action", MySqlDbType.Enum);
			insertQry.Parameters.Add("?ankunft", MySqlDbType.UInt32);
			insertQry.Parameters.Add("?nummer", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?firstseen", MySqlDbType.UInt32);
            insertQry.Parameters.Add("?notyetseen", MySqlDbType.UInt32);
			insertQry.Prepare();
			MySqlCommand deleteQry = new MySqlCommand(@"DELETE FROM " + DBPrefix + @"flotten WHERE id=?id", con);
			deleteQry.Parameters.Add("?id", MySqlDbType.UInt32);
			deleteQry.Prepare();
            uint now = IWDBUtils.toUnixTimestamp(DateTime.Now);

			foreach(Match outerMatch in matches) {
				MatchCollection innerMatches = Regex.Matches(outerMatch.Groups[0].Value, "(" + KolonieName + @")\s" + KoordinatenEinzelMatch + @"\s+(" + KolonieName + @")\s" + KoordinatenEinzelMatch + @"\n
(" + SpielerName + @")\s+(" + PräziseIWZeit + @"|Plündert,\sMordet\sund\sBrandschatzt)[\s\-]+.*?\s+Angriff", RegexOptions.IgnorePatternWhitespace);
				if(innerMatches.Count == 0) {
					resp.RespondError("Hab hier feindliche Flotten ohne Flotten! Evtl läuft da was schief.. bitte mal melden!");
					continue;
				}
				OrderedList<FlottenCacheFlotte> flotten = new OrderedList<FlottenCacheFlotte>(new FlottenComparer());
				uint uid = 0;
				Dictionary<uint, Pair<string, uint>> ownerCount = new Dictionary<uint, Pair<string,uint>>();
				for(int i = 0; i < innerMatches.Count; ++i) {
					uidQuery.Parameters["?gala"].Value = int.Parse(innerMatches[i].Groups[2].Value);
					uidQuery.Parameters["?sys"].Value = int.Parse(innerMatches[i].Groups[3].Value);
					uidQuery.Parameters["?pla"].Value = int.Parse(innerMatches[i].Groups[4].Value);
					MySqlDataReader r = uidQuery.ExecuteReader();
					try {
						if(!r.Read())
							continue;
						uid = (uint)r.GetUInt32(0);
						if(ownerCount.ContainsKey(uid))
							ownerCount[uid].Item2++;
						else
							ownerCount.Add(uid, new Pair<string, uint>(r.GetString(1), 1));
					} finally {
						r.Close();
					}
				}
				Tuple<uint, string, uint> ownerData = ownerCache.Select(key => { Pair<string, uint> val; return ownerCount.TryGetValue(key, out val) ? new Tuple<uint, string, uint>(key, val.Item1, val.Item2) : new Tuple<uint, string, uint>(key, "", 0); }).Aggregate(new Tuple<uint, string, uint>(0, "", 0), (p1, p2) => p1.Item3 >= p2.Item3 ? p1 : p2); //heres to hopin noone ever has to understand that
				uid = ownerData.Item1;
				if(uid == 0 || ownerData.Item3 == 0) {
					resp.RespondError("Mir ist kein einziger der von feindlichen Flotten angeflogenen Zielplanis (" + innerMatches[0].Groups[2].Value + ":" + innerMatches[0].Groups[3].Value + ":" + innerMatches[0].Groups[4].Value + ") bekannt: Die gesamten feindlichen Flotten wurden übersprungen!\n");
					continue;
				}

				foreach(Match m in innerMatches) {
					if(m.Groups[10].Value == "Plündert, Mordet und Brandschatzt")
						continue;
					Dictionary<uint, uint> arrivalsAtTime = new Dictionary<uint, uint>();
					uint zielID = idFetcherZielID.GetID(uint.Parse(m.Groups[2].Value), uint.Parse(m.Groups[3].Value), uint.Parse(m.Groups[4].Value), m.Groups[1].Value, ownerData.Item2);
					uint startID = idFetcherStartID.GetID(uint.Parse(m.Groups[6].Value), uint.Parse(m.Groups[7].Value), uint.Parse(m.Groups[8].Value), m.Groups[5].Value, m.Groups[9].Value);
					uint time = IWDBUtils.parsePreciseIWTime(m.Groups[10].Value);
					uint num = arrivalsAtTime.ContainsKey(time) ? ++arrivalsAtTime[time] : (arrivalsAtTime[time] = 0);
					FlottenCacheFlotte flotte = new FlottenCacheFlotte(0, startID, zielID, time, num, 0, "Angriff");
					flotten.Add(flotte);
				}
				OrderedList<FlottenCacheFlotte> cachedFlotten;
				if(!flottenCache.TryGetValue(uid, out cachedFlotten)) {
					cachedFlotten = new OrderedList<FlottenCacheFlotte>(new FlottenComparer());
				}
				List<OrderedListDifference<FlottenCacheFlotte>> diffs = cachedFlotten.Differences(flotten);
				int neu = 0;
				String ziel = "";
				foreach(OrderedListDifference<FlottenCacheFlotte> diff in diffs) {
					if(diff.Item.Action != "Angriff")
						continue;
					if(diff.Difference == OrderedListDifferenceType.MissingInCompared) {
						if(diff.Item.ankunft < (now-900) || diff.Item.ankunft > now) { //failsafe gegen Chemiemangel, dafür "werden flotten nicht recalled"
							deleteQry.Parameters["?id"].Value = diff.Item.id;
							deleteQry.ExecuteNonQuery();
						}
					} else {
						insertQry.Parameters["?start"].Value = diff.Item.startid;
						insertQry.Parameters["?ziel"].Value = diff.Item.zielid;
						insertQry.Parameters["?action"].Value = "Angriff";
						insertQry.Parameters["?ankunft"].Value = diff.Item.ankunft;
						insertQry.Parameters["?nummer"].Value = diff.Item.nummer;
                        insertQry.Parameters["?firstseen"].Value = now;
                        insertQry.Parameters["?notyetseen"].Value = oldLastParsed.GetOrDefault(uid, (uint)0);
						insertQry.ExecuteNonQuery();
						if(neu == 0) {
							MySqlCommand zielQry = new MySqlCommand("SELECT ownername FROM " + DBPrefix + "universum WHERE id=?id", con);
							zielQry.Parameters.Add("?id", MySqlDbType.UInt32).Value = diff.Item.zielid;
							ziel = zielQry.ExecuteScalar() as String;
						}
						++neu;
					}
				}
				if(neu > 0)
					parser.NeueFlottenGesichtet(ziel, neu, true);
				cachedFlotten.RemoveMatching(delegate(FlottenCacheFlotte f) { return f.Action == "Angriff"; }); //wird von der Koloinfo wieder befüllt
				resp.Respond("Feindliche Flotten ("+ownerData.Item2+") eingelesen!\n");
			}
		}
	}

	class FlottenCleanupPostRequestHandler : IPostRequestHandler {
		Dictionary<uint, OrderedList<FlottenCacheFlotte>> flottenCache;
		public FlottenCleanupPostRequestHandler(NewscanHandler h) {
			flottenCache = h.RequestCache<Dictionary<uint, OrderedList<FlottenCacheFlotte>>>("FlottenCache");
		}
		public void HandlePostRequest(MySqlConnection con, String DBPrefix) {
			if(flottenCache.Count > 0) {
				MySqlCommand cleanupCmd = new MySqlCommand("DELETE FROM " + DBPrefix + "flotten WHERE id=?id", con);
				cleanupCmd.Parameters.Add("?id", MySqlDbType.UInt32);
				cleanupCmd.Prepare();
				foreach(OrderedList<FlottenCacheFlotte> ol in flottenCache.Values) {
					if(ol.Count > 0) {
						foreach(FlottenCacheFlotte fl in ol) {
							cleanupCmd.Parameters["?id"].Value = fl.id;
							cleanupCmd.ExecuteNonQuery();
						}
					}
				}
				flottenCache.Clear();
			}
		}

		public string Name {
			get { return "flottenCleanup"; }
		}
	}
class FlottenComparer : IComparer<FlottenCacheFlotte> {
		public int Compare(FlottenCacheFlotte x, FlottenCacheFlotte y) {
			int diff = (int)(x.ankunft - y.ankunft);
			if (diff != 0)
				return diff;
			diff = (int)x.nummer - (int)y.nummer;
			if(diff != 0)
				return diff;
			diff = (int)(x.startid - y.startid);
			if (diff != 0)
				return diff;
			diff = (int)(x.zielid - y.zielid);
			if (diff != 0)
				return diff;
			return x.Action.CompareTo(y.Action);
		}
	}
	class FlottenCacheFlotte {
		public FlottenCacheFlotte(uint id, uint startid, uint zielid, uint ankunft, uint nummer, uint erinnerungsstatus, String action) {
			this.id = id;
			this.startid = startid;
			this.zielid = zielid;
			this.ankunft = ankunft;
			this.nummer = nummer;
			this.erinnerungsstatus = erinnerungsstatus;
			this.Action = action;
		}
		public readonly uint id;
		public readonly uint startid;
		public readonly uint zielid;
		public readonly uint ankunft;
		public readonly uint nummer;
		public readonly uint erinnerungsstatus;
		public readonly String Action;
	}
}