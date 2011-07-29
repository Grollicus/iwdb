using System;
using System.Collections.Generic;
using System.Text;
using MySql.Data.MySqlClient;
using System.IO;
using System.Net;
using System.Text.RegularExpressions;
using System.Linq;

namespace IWDB {
	static class IWDBUtils {
		public static uint parseIWTime(String toParse) {
			//03.04.2008 11:27
			return toUnixTimestamp(DateTime.ParseExact(toParse, "dd.MM.yyyy HH:mm", null, System.Globalization.DateTimeStyles.AssumeLocal|System.Globalization.DateTimeStyles.AdjustToUniversal));
		}
		public static uint parsePreciseIWTime(String toParse) {
			//03.04.2008 11:27:45
			try {
				return toUnixTimestamp(DateTime.ParseExact(toParse, "dd.MM.yyyy HH:mm:ss", null, System.Globalization.DateTimeStyles.AssumeLocal | System.Globalization.DateTimeStyles.AdjustToUniversal));
			} catch (FormatException) {
				return toUnixTimestamp(DateTime.Now);
			}
		}
		public static uint toUnixTimestamp(DateTime time) {
			//return (uint)((time.ToUniversalTime().Ticks - 621355968000000000) / 10000000);
			if(time.Kind == DateTimeKind.Utc)
				return (uint)(time - new DateTime(1970, 1, 1, 0, 0, 0, DateTimeKind.Utc)).TotalSeconds;
			else
				return (uint)(time.ToUniversalTime() - new DateTime(1970, 1, 1, 0, 0, 0, DateTimeKind.Utc)).TotalSeconds;
		}
		public static DateTime fromUnixTimestamp(uint timestamp) {
            //TimeSpan sp = new TimeSpan(timestamp * (long)10000000);
			return new DateTime(1970, 1, 1, 0, 0, 0, 0, DateTimeKind.Utc).AddSeconds(timestamp).ToLocalTime();
			//return new DateTime((long)timestamp * 10000000 + 621355968000000000, DateTimeKind.Utc);
		}
        public static T2 Get<T1, T2>(Dictionary<T1, T2> dict, T1 key, T2 defaultValue) {
            T2 ret;
            if (dict.TryGetValue(key, out ret))
                return ret;
            return defaultValue;
        }
		public static T2 GetOrDefault<T1, T2>(this Dictionary<T1, T2> dict, T1 key, T2 defaultValue) {
			T2 ret;
			if(dict.TryGetValue(key, out ret))
				return ret;
			return defaultValue;
		}
    }
	abstract class IWDBRegex {
		public const String KolonieName = @"(?:(?:[a-zA-Z0-9_\-\.äöüÄÖÜß*][a-zA-Z0-9_\-\. äöüÄÖÜß*]*[a-zA-Z0-9_\-\.äöüÄÖÜß*])|[a-zA-Z0-9_\-\.äöüÄÖÜß*])";
		public const String SpielerName = @"[a-zA-Z0-9_\- \.]+";
		public const String AllyTag = @"(?:\[[a-zA-Z0-9\-_\.\{\}]+\])";
		public const String Koordinaten = @"\(\d+:\d+:\d+\)";
		public const String KoordinatenMatch = @"\((\d+:\d+:\d+)\)";
		public const String KoordinatenEinzelMatch = @"\((\d+):(\d+):(\d+)\)";
		public const String IWZeit = @"\d{2}\.\d{2}\.\d{4}\s\d{2}:\d{2}";
		public const String PräziseIWZeit = @"\d{2}\.\d{2}\.\d{4}\s\d{2}:\d{2}:\d{2}";
		public const String IWZeitspanne = @"(?:(?:1\s+Tag\s+)|(?:\d+\s+Tage\s+))?\d{2}:\d{2}:\d{2}";
		public const String IWObjektTyp = @"Kolonie|Sammelbasis|Kampfbasis|Artefaktbasis|---";
		public const String IWPlanetenTyp = @"Steinklumpen|Eisplanet|Gasgigant|Nichts|Asteroid|Elektrosturm|Raumverzerrung|Ionensturm|grav.\sAnomalie";
		public static String Number { get { return "[0-9" + System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator + "]+"; } }
		public const String RessourcenName = @"(?:Eisen|Stahl|VV4A|chem\.\sElemente|Eis|Wasser|Energie|Pinguine|Credits|Bevölkerung)";
		public const String AbladeAktionen = @"Erforscht\sgrade\sseine\sNase|Erklaert\sdie\sInfinitesimalrechnung|Faselt\swas\svon\sWurzelzwergen|Faselt\swirres\sZeug\sins\sInterkom|Hört\sMusik|Im\sLandeanflug|Liegt\sbesoffen\sin\sder\sEcke|Pfeift\sder\sfeschen\sPilotin\shinterher\sund\smacht\skomische\sAndeutungen|Quatscht\smit\sder\sBodenkontrolle|Schaut\sder\sfeschen\sPilotin\shinterher|Sabbert\sdie\sInstrumente\svoll|Surft\sim\sBordnetz|Versucht\sdie\srichtigen\sKnöpfe\sfür\sdie\sLandung\szu\sfinden|Wartet\sauf\sLandeplatz|Wartet\sauf\sWeihnachten|Wurzelzwergen,\süberall\sWurzelzwergen|Wartet\sauf\sWeihnachten|Bewertet\sdas\sFlottenessen";
	}
	public class Pair<T1, T2> {
		public T1 Item1;
		public T2 Item2;
		public Pair(T1 item1, T2 item2) {
			this.Item1 = item1;
			this.Item2 = item2;
		}
		public Pair() {
			this.Item1 = default(T1);
			this.Item2 = default(T2);
		}
	}
    

    [Flags]
    enum PlaniDataFlags {
        _Geoscan = 4096,
        _Allytags = 1024,
        _IgmData = 536870912,

        AllUni = ID | IWID | Sys | Gala | Pla | TimeStamp | Planityp | Objekttyp | Ownername | Planiname,
        //AllAllytag = Allytag,
        AllGeoscan = Eisen | Chemie | Eis | Gravi | Lbed | Gebmod | Gebtimemod | Shipmod | Shiptimemod | TtChemie | TtEis | TtEisen | GeoTimeStamp,
        //AllIgmData = IgmID,

        ID = 1,
        IWID = 2,
        Sys = 4,
        Gala = 8,
        Pla = 16,
        TimeStamp = 32,
        Planityp = 64,
        Objekttyp = 128,
        Ownername = 256,
        Planiname = 512,
        Allytag = _Allytags | 2048,
        Eisen = _Geoscan | 8192,
        Chemie = _Geoscan | 16384,
        Eis = _Geoscan | 32768,
        Gravi = _Geoscan | 65536,
        Lbed = _Geoscan | 131072,
        Nebel = _Geoscan | 262144,
        Besonderheiten = _Geoscan | 524288,
        Fmod = _Geoscan | 1048576,
        Gebmod = _Geoscan | 2097152,
        Gebtimemod = _Geoscan | 4194304,
        Shipmod = _Geoscan | 8388608,
        Shiptimemod = _Geoscan | 16777216,
        TtEisen = _Geoscan | 33554432,
        TtChemie = _Geoscan | 67108864,
        TtEis = _Geoscan | 134217728,
        GeoTimeStamp = _Geoscan | 268435456,
        IgmID = _IgmData | 1073741824,

    }
    abstract class PlaniInfo {

        protected static Dictionary<PlaniDataFlags, String> cols;
        static PlaniInfo() {
            cols = new Dictionary<PlaniDataFlags, string>();
            cols.Add(PlaniDataFlags.Allytag, "uni_userdata.allytag");
            cols.Add(PlaniDataFlags.Besonderheiten, "geoscans.besonderheiten");
            cols.Add(PlaniDataFlags.Chemie, "geoscans.chemie");
            cols.Add(PlaniDataFlags.Eis, "geoscans.eis");
            cols.Add(PlaniDataFlags.Eisen, "geoscans.eisen");
            cols.Add(PlaniDataFlags.Fmod, "geoscans.fmod");
            cols.Add(PlaniDataFlags.Gala, "universum.gala");
            cols.Add(PlaniDataFlags.Gebmod, "geoscans.gebmod");
            cols.Add(PlaniDataFlags.Gebtimemod, "geoscans.gebtimemod");
            cols.Add(PlaniDataFlags.GeoTimeStamp, "geoscans.timestamp");
            cols.Add(PlaniDataFlags.Gravi, "geoscans.gravi");
            cols.Add(PlaniDataFlags.ID, "universum.ID");
            cols.Add(PlaniDataFlags.IWID, "universum.iwid");
            cols.Add(PlaniDataFlags.Lbed, "geoscans.lbed");
            cols.Add(PlaniDataFlags.Nebel, "geoscans.nebel");
            cols.Add(PlaniDataFlags.Objekttyp, "universum.objekttyp");
            cols.Add(PlaniDataFlags.Ownername, "universum.ownername");
            cols.Add(PlaniDataFlags.Pla, "universum.pla");
            cols.Add(PlaniDataFlags.Planiname, "universum.planiname");
            cols.Add(PlaniDataFlags.Planityp, "universum.planityp");
            cols.Add(PlaniDataFlags.Shipmod, "geoscans.shipmod");
            cols.Add(PlaniDataFlags.Shiptimemod, "geoscans.shiptimemod");
            cols.Add(PlaniDataFlags.Sys, "universum.sys");
            cols.Add(PlaniDataFlags.TimeStamp, "universum.inserttime");
            cols.Add(PlaniDataFlags.TtChemie, "geoscans.tt_chemie");
            cols.Add(PlaniDataFlags.TtEis, "geoscans.tt_eis");
            cols.Add(PlaniDataFlags.TtEisen, "geoscans.tt_eisen");
            cols.Add(PlaniDataFlags.IgmID, "igm_data.ID");
        }

        protected UInt32 iD, iWID, sys, gala, pla, timeStamp, geoTimeStamp, igmID;
        protected UInt32 eisen, chemie, eis, gravi, lbed, fmod, gebmod, gebtimemod, shipmod, shiptimemod, ttEisen, ttChemie, ttEis;
        protected Int32 besonderheiten, nebel;
        protected String planityp, objekttyp, ownername, planiname, allytag;

        protected PlaniDataFlags whatIGot;
        protected bool writeable;

        protected internal IWDB.Parser.BesonderheitenData besData;
        protected internal MySqlConnection con;
        protected internal String DBPrefix;

        protected PlaniInfo() {
            whatIGot = 0;
        }
        public abstract PlaniUpdater Update();
        
        #region Accessoren

        private void Check(PlaniDataFlags shouldHave) {
            if((whatIGot & shouldHave) != shouldHave)
                throw new InvalidOperationException();
        }
        private void Got(PlaniDataFlags haveNow) {
            if (!writeable)
                throw new InvalidOperationException("Darf nicht geschrieben werden!");
            whatIGot = whatIGot | haveNow;
        }

        public UInt32 ID { get { Check(PlaniDataFlags.ID); return iD; } set { Got(PlaniDataFlags.ID); iD = value; } }
        public UInt32 IWID { get { Check(PlaniDataFlags.IWID); return iWID; } set { Got(PlaniDataFlags.IWID); iWID = value; } }
        public UInt32 Sys { get { Check(PlaniDataFlags.Sys); return sys; } set { Got(PlaniDataFlags.Sys); sys = value; } }
        public UInt32 Gala { get { Check(PlaniDataFlags.Gala); return gala; } set { Got(PlaniDataFlags.Gala); gala = value; } }
        public UInt32 Pla { get { Check(PlaniDataFlags.Pla); return pla; } set { Got(PlaniDataFlags.Pla); pla = value; } }
        public DateTime TimeStamp { get { Check(PlaniDataFlags.TimeStamp); return IWDBUtils.fromUnixTimestamp(timeStamp); } set { Got(PlaniDataFlags.TimeStamp); timeStamp = IWDBUtils.toUnixTimestamp(value); } }
        public String Planityp { get { Check(PlaniDataFlags.Planityp); return planityp; } set { Got(PlaniDataFlags.Planityp); planityp = value; } }
        public String Objekttyp { get { Check(PlaniDataFlags.Objekttyp); return objekttyp; } set { Got(PlaniDataFlags.Objekttyp); objekttyp = value; } }
        public String Ownername { get { Check(PlaniDataFlags.Ownername); return ownername; } set { Got(PlaniDataFlags.Ownername); ownername = value; } }
        public String Planiname { get { Check(PlaniDataFlags.Planiname); return planiname; } set { Got(PlaniDataFlags.Planiname); planiname = value; } }
        public String Allytag { get { Check(PlaniDataFlags.Allytag); return allytag; } set { Got(PlaniDataFlags.Allytag); allytag = value; } }

        public DateTime GeoTimeStamp { get { Check(PlaniDataFlags.GeoTimeStamp); return IWDBUtils.fromUnixTimestamp(geoTimeStamp); } set { Got(PlaniDataFlags.GeoTimeStamp); geoTimeStamp = IWDBUtils.toUnixTimestamp(value); } }
        public float Eisen { get { Check(PlaniDataFlags.Eisen); return eisen / 1000.0f; } set { Got(PlaniDataFlags.Eisen); eisen = (uint)(value * 1000); } }
        public float Chemie { get { Check(PlaniDataFlags.Chemie); return chemie / 1000.0f; } set { Got(PlaniDataFlags.Chemie); chemie = (uint)(value * 1000); } }
        public float Eis { get { Check(PlaniDataFlags.Eis); return eis / 1000.0f; } set { Got(PlaniDataFlags.Eis); eis = (uint)(value * 1000); } }
        public float Lbed { get { Check(PlaniDataFlags.Lbed); return lbed / 1000.0f; } set { Got(PlaniDataFlags.Lbed); lbed = (uint)(value * 1000); } }
        public float Gravi { get { Check(PlaniDataFlags.Gravi); return gravi / 100.0f; } set { Got(PlaniDataFlags.Gravi); gravi = (uint)(value * 100); } }
        public String Nebel { get { Check(PlaniDataFlags.Nebel); return besData.NebelDecode(nebel); } set { Got(PlaniDataFlags.Nebel); nebel = besData.NebelEncode(value); } }
        public List<String> Besonderheiten { get { Check(PlaniDataFlags.Besonderheiten); return besData.BesonderheitenDecode(besonderheiten); } set { Got(PlaniDataFlags.Besonderheiten); besonderheiten  = besData.BesonderheitenEncode(value); } }
        
        public float Fmod { get { Check(PlaniDataFlags.Fmod); return fmod / 100.0f; } set { Got(PlaniDataFlags.Fmod); fmod = (uint)(value * 100); } }
        public float Gebmod { get { Check(PlaniDataFlags.Gebmod); return gebmod / 100.0f; } set { Got(PlaniDataFlags.Gebmod); gebmod = (uint)(value * 100); } }
        public float Gebtimemod { get { Check(PlaniDataFlags.Gebtimemod); return gebtimemod / 100.0f; } set { Got(PlaniDataFlags.Gebtimemod); gebtimemod = (uint)(value * 100); } }
        public float Shipmod { get { Check(PlaniDataFlags.Shipmod); return shipmod / 100.0f; } set { Got(PlaniDataFlags.Shipmod); shipmod = (uint)(value * 100); } }
        public float Shiptimemod { get { Check(PlaniDataFlags.Shiptimemod); return shiptimemod / 100.0f; } set { Got(PlaniDataFlags.Shiptimemod); shiptimemod = (uint)(value * 100); } }
        public float TtEisen { get { Check(PlaniDataFlags.TtEisen); return ttEisen / 1000.0f; } set { Got(PlaniDataFlags.TtEisen); ttEisen = (uint)(value * 1000); } }
        public float TtChemie { get { Check(PlaniDataFlags.TtChemie); return ttChemie / 1000.0f; } set { Got(PlaniDataFlags.TtChemie); ttChemie = (uint)(value * 1000); } }
        public float TtEis { get { Check(PlaniDataFlags.TtEis); return ttEis / 1000.0f; } set { Got(PlaniDataFlags.TtEis); ttEis = (uint)(value * 1000); } }

        public UInt32 IgmUserID { get { Check(PlaniDataFlags.IgmID); return igmID; } set { Got(PlaniDataFlags.IgmID); igmID = value; } }
        #endregion
    }
    class PlaniFetcher : PlaniInfo {
        public PlaniFetcher(IWDB.Parser.BesonderheitenData besData, MySqlConnection con, String DBPrefix) {
            writeable = true;
            this.besData = besData;
            this.con = con;
            this.DBPrefix = DBPrefix;
        }

        public override PlaniUpdater Update() {
            return new PlaniUpdater(this);
        }

        private PlaniFetcher(PlaniDataFlags whatIGot, MySqlDataReader data, MySqlConnection con, String DBPrefix, IWDB.Parser.BesonderheitenData besData) {
            writeable = false;
            this.besData = besData;
            this.con = con;
            this.DBPrefix = DBPrefix;
            this.whatIGot = whatIGot;

            int i = 0;
            foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                if ((whatIGot & flag) != flag || !cols.ContainsKey(flag))
                    continue;
                switch (flag) {
                    case PlaniDataFlags.Allytag:
                        allytag = data.GetString(i);
                        break;
                    case PlaniDataFlags.Besonderheiten:
                        besonderheiten = data.GetInt32(i);
                        break;
                    case PlaniDataFlags.Chemie:
                        chemie = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Eis:
                        eis = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Eisen:
                        eisen = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Fmod:
                        fmod = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Gala:
                        gala = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Gebmod:
                        gebmod = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Gebtimemod:
                        gebtimemod = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.GeoTimeStamp:
                        geoTimeStamp = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Gravi:
                        gravi = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.ID:
                        iD = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.IgmID:
                        igmID = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.IWID:
                        iWID = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Lbed:
                        lbed = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Nebel:
                        nebel = data.GetInt32(i);
                        break;
                    case PlaniDataFlags.Objekttyp:
                        objekttyp = data.GetString(i);
                        break;
                    case PlaniDataFlags.Ownername:
                        ownername = data.GetString(i);
                        break;
                    case PlaniDataFlags.Pla:
                        pla = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Planiname:
                        planiname = data.GetString(i);
                        break;
                    case PlaniDataFlags.Planityp:
                        planityp = data.GetString(i);
                        break;
                    case PlaniDataFlags.Shipmod:
                        shipmod = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Shiptimemod:
                        shiptimemod = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.Sys:
                        sys = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.TimeStamp:
                        timeStamp = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.TtChemie:
                        ttChemie = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.TtEis:
                        ttEis = data.GetUInt32(i);
                        break;
                    case PlaniDataFlags.TtEisen:
                        ttEisen = data.GetUInt32(i);
                        break;
                }
                ++i;
            }
        }

        public List<PlaniInfo> FetchMatching(PlaniDataFlags whatIWant) {
            if (whatIGot == 0 || whatIWant == 0)
                throw new InvalidOperationException("Will nix oder hab nix!");
            StringBuilder sb = new StringBuilder("SELECT ");
            foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                if (!cols.ContainsKey(flag) || (flag & whatIWant) != flag)
                    continue;
                sb.Append(cols[flag]);
                sb.Append(", ");
            }
            sb.Length = sb.Length - 2;
            sb.Append(" FROM ((");
            sb.Append(DBPrefix);
            sb.Append("universum AS universum");
            if (((whatIGot|whatIWant) & PlaniDataFlags._Allytags) == PlaniDataFlags._Allytags) {
                sb.Append(" INNER JOIN ");
                sb.Append(DBPrefix);
                sb.Append("uni_userdata AS uni_userdata ON universum.ownername = uni_userdata.name");
            }
            sb.Append(")");
            if (((whatIGot | whatIWant) & PlaniDataFlags._Geoscan) == PlaniDataFlags._Geoscan) {
                sb.Append(" INNER JOIN ");
                sb.Append(DBPrefix);
                sb.Append("geoscans AS geoscans ON universum.ID = geoscans.id");
            } 
            sb.Append(")");
            if (((whatIGot | whatIWant) & PlaniDataFlags._IgmData) == PlaniDataFlags._IgmData) {
                sb.Append(" INNER JOIN ");
                sb.Append(DBPrefix);
                sb.Append("igm_data AS igm_data ON universum.ownername = igm_data.igmname");
            }
            sb.Append(" WHERE ");
            foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                if (!cols.ContainsKey(flag) || (flag & whatIGot) != flag)
                    continue;
                sb.Append(cols[flag]);
                sb.Append("=?");
                sb.Append(cols[flag]);
                sb.Append(" AND ");
            }
            sb.Length = sb.Length - 5;
            MySqlCommand qry = new MySqlCommand(sb.ToString(), con);
            foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                if (!cols.ContainsKey(flag) || (flag & whatIGot) != flag)
                    continue;
                switch (flag) {
                    case PlaniDataFlags.Allytag:
                        qry.Parameters.Add(cols[flag], MySqlDbType.String).Value = allytag;
                        break;
                    case PlaniDataFlags.Besonderheiten:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = besonderheiten;
                        break;
                    case PlaniDataFlags.Chemie:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = chemie;
                        break;
                    case PlaniDataFlags.Eis:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = eis;
                        break;
                    case PlaniDataFlags.Eisen:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = eisen;
                        break;
                    case PlaniDataFlags.Fmod:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = fmod;
                        break;
                    case PlaniDataFlags.Gala:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gala;
                        break;
                    case PlaniDataFlags.Gebmod:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gebmod;
                        break;
                    case PlaniDataFlags.Gebtimemod:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gebtimemod;
                        break;
                    case PlaniDataFlags.GeoTimeStamp:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = geoTimeStamp;
                        break;
                    case PlaniDataFlags.Gravi:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gravi;
                        break;
                    case PlaniDataFlags.ID:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = iD;
                        break;
                    case PlaniDataFlags.IgmID:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = igmID;
                        break;
                    case PlaniDataFlags.IWID:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = iWID;
                        break;
                    case PlaniDataFlags.Lbed:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = lbed;
                        break;
                    case PlaniDataFlags.Nebel:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = nebel;
                        break;
                    case PlaniDataFlags.Objekttyp:
                        qry.Parameters.Add(cols[flag], MySqlDbType.String).Value = objekttyp;
                        break;
                    case PlaniDataFlags.Ownername:
                        qry.Parameters.Add(cols[flag], MySqlDbType.String).Value = ownername;
                        break;
                    case PlaniDataFlags.Pla:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = pla;
                        break;
                    case PlaniDataFlags.Planiname:
                        qry.Parameters.Add(cols[flag], MySqlDbType.String).Value = planiname;
                        break;
                    case PlaniDataFlags.Planityp:
                        qry.Parameters.Add(cols[flag], MySqlDbType.String).Value = planityp;
                        break;
                    case PlaniDataFlags.Shipmod:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = shipmod;
                        break;
                    case PlaniDataFlags.Shiptimemod:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = shiptimemod;
                        break;
                    case PlaniDataFlags.Sys:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = sys;
                        break;
                    case PlaniDataFlags.TimeStamp:
                        qry.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = timeStamp;
                        break;
                    case PlaniDataFlags.TtChemie:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttChemie;
                        break;
                    case PlaniDataFlags.TtEis:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttEis;
                        break;
                    case PlaniDataFlags.TtEisen:
                        qry.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttEisen;
                        break;
                }
            }
            MySqlDataReader r = qry.ExecuteReader();
            List<PlaniInfo> ret = new List<PlaniInfo>();
            while (r.Read()) {
                ret.Add(new PlaniFetcher(whatIWant, r, con, DBPrefix, besData));
            }
            r.Close();
            return ret;
        }
        public PlaniUpdater GetUpdater() {
            if ((whatIGot & PlaniDataFlags.ID) == 0)
                throw new InvalidOperationException();
            return new PlaniUpdater(this);
        }
    }
    class PlaniUpdater : PlaniInfo {
        PlaniFetcher match;

        internal PlaniUpdater(PlaniFetcher match) {
            this.match = match;
            this.con = match.con;
            this.DBPrefix = match.DBPrefix;
            this.besData = match.besData;
            this.writeable = true;
        }

        public void Save() {
            List<PlaniInfo> planis = match.FetchMatching(PlaniDataFlags.ID);
            if ((whatIGot & PlaniDataFlags.ID) != 0)
                throw new InvalidOperationException("Cannot write ID!");
            IEnumerable<uint> ids = from plani in planis select plani.ID;

            if ((whatIGot & PlaniDataFlags._Allytags) == PlaniDataFlags._Allytags) {
                StringBuilder qry = new StringBuilder("INSERT INTO ");
                qry.Append(DBPrefix);
                qry.Append("uni_userdata (");
                qry.Append(cols[PlaniDataFlags.Allytag]);
                qry.Append(", ID) VALUES (?");
                qry.Append(cols[PlaniDataFlags.Allytag]);
                qry.Append(", ?id) ON DUPLICATE KEY UPDATE ");
                qry.Append(DBPrefix);
                qry.Append(cols[PlaniDataFlags.Allytag]);
                qry.Append("=VALUES(");
                qry.Append(DBPrefix);
                qry.Append(cols[PlaniDataFlags.Allytag]);
                qry.Append(")");
                MySqlCommand cmd = new MySqlCommand(qry.ToString(), con);
                cmd.Parameters.Add(cols[PlaniDataFlags.Allytag], MySqlDbType.String).Value = allytag;
                cmd.Parameters.Add("id", MySqlDbType.UInt32);
                cmd.Prepare();
                foreach (uint id in ids) {
                    cmd.Parameters["id"].Value = id;
                    cmd.ExecuteNonQuery();
                }
            }

            if ((whatIGot & PlaniDataFlags._Geoscan) == PlaniDataFlags._Geoscan) {
                StringBuilder qryStr = new StringBuilder("INSERT INTO ");
                StringBuilder valuesStr = new StringBuilder();
                StringBuilder updateStr = new StringBuilder();
                qryStr.Append(DBPrefix);
                qryStr.Append("geoscans (id, ");
                foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                    if ((whatIGot & flag) == flag && (flag & PlaniDataFlags._Geoscan) == PlaniDataFlags._Geoscan && cols.ContainsKey(flag)) {
                        qryStr.Append(DBPrefix);
                        qryStr.Append(cols[flag]);
                        qryStr.Append(", ");
                        valuesStr.Append("?");
                        valuesStr.Append(cols[flag]);
                        valuesStr.Append(", ");
                        updateStr.Append(DBPrefix);
                        updateStr.Append(cols[flag]);
                        updateStr.Append("=VALUES(");
                        updateStr.Append(DBPrefix);
                        updateStr.Append(cols[flag]);
                        updateStr.Append("), ");
                    }
                }
                qryStr.Length = qryStr.Length - 2;
                valuesStr.Length = valuesStr.Length - 2;
                updateStr.Length = updateStr.Length - 2;
                qryStr.Append(") VALUES (?id, ");
                qryStr.Append(valuesStr);
                qryStr.Append(") ON DUPLICATE KEY UPDATE ");
                qryStr.Append(updateStr);
                MySqlCommand cmd = new MySqlCommand(qryStr.ToString(), con);
                foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                    if ((whatIGot & flag) == flag && (flag & PlaniDataFlags._Geoscan) == PlaniDataFlags._Geoscan && cols.ContainsKey(flag)) {
                        switch (flag) {
                            case PlaniDataFlags.Besonderheiten:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = besonderheiten;
                                break;
                            case PlaniDataFlags.Chemie:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = chemie;
                                break;
                            case PlaniDataFlags.Eis:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = eis;
                                break;
                            case PlaniDataFlags.Eisen:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = eisen;
                                break;
                            case PlaniDataFlags.Fmod:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = fmod;
                                break;
                            case PlaniDataFlags.Gebmod:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gebmod;
                                break;
                            case PlaniDataFlags.Gebtimemod:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gebtimemod;
                                break;
                            case PlaniDataFlags.GeoTimeStamp:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = geoTimeStamp;
                                break;
                            case PlaniDataFlags.Gravi:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gravi;
                                break;
                            case PlaniDataFlags.Lbed:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = lbed;
                                break;
                            case PlaniDataFlags.Nebel:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = nebel;
                                break;
                            case PlaniDataFlags.Shipmod:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = shipmod;
                                break;
                            case PlaniDataFlags.Shiptimemod:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = shiptimemod;
                                break;
                            case PlaniDataFlags.TtChemie:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttChemie;
                                break;
                            case PlaniDataFlags.TtEis:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttEis;
                                break;
                            case PlaniDataFlags.TtEisen:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.Int32).Value = ttEisen;
                                break;
                        }
                    }
                }
                cmd.Parameters.Add("id", MySqlDbType.UInt32);
                cmd.Prepare();
                foreach (uint id in ids) {
                    cmd.Parameters["id"].Value = id;
                    cmd.ExecuteNonQuery();
                }
            }

            if ((whatIGot & PlaniDataFlags.AllUni) != 0) {
                StringBuilder qry = new StringBuilder("INSERT INTO ");
                StringBuilder valuesStr = new StringBuilder();
                StringBuilder updateStr = new StringBuilder();
                qry.Append(DBPrefix);
                qry.Append("universum (id, ");
                foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                    if ((whatIGot & flag) == flag && (flag & (PlaniDataFlags._Geoscan | PlaniDataFlags._Allytags)) == 0 && cols.ContainsKey(flag)) {
                        qry.Append(DBPrefix);
                        qry.Append(cols[flag]);
                        qry.Append(", ");
                        valuesStr.Append("?");
                        valuesStr.Append(cols[flag]);
                        valuesStr.Append(", ");
                        updateStr.Append(DBPrefix);
                        updateStr.Append(cols[flag]);
                        updateStr.Append("=VALUES(");
                        updateStr.Append(DBPrefix);
                        updateStr.Append(cols[flag]);
                        updateStr.Append("), ");
                    }
                }
                qry.Length = qry.Length - 2;
                valuesStr.Length = valuesStr.Length - 2;
                updateStr.Length = updateStr.Length - 2;
                qry.Append(") VALUES (?id, ");
                qry.Append(valuesStr);
                qry.Append(") ON DUPLICATE KEY UPDATE ");
                qry.Append(updateStr);
                MySqlCommand cmd = new MySqlCommand(qry.ToString(), con);
                foreach (PlaniDataFlags flag in Enum.GetValues(typeof(PlaniDataFlags))) {
                    if ((whatIGot & flag) == flag && (flag & (PlaniDataFlags._Geoscan|PlaniDataFlags._Allytags)) == 0 && cols.ContainsKey(flag)) {
                        switch (flag) {
                            case PlaniDataFlags.Gala:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = gala;
                                break;
                            case PlaniDataFlags.IWID:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = iWID;
                                break;
                            case PlaniDataFlags.Objekttyp:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.String).Value = objekttyp;
                                break;
                            case PlaniDataFlags.Ownername:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.String).Value = ownername;
                                break;
                            case PlaniDataFlags.Pla:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt16).Value = pla;
                                break;
                            case PlaniDataFlags.Planiname:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.String).Value = planiname;
                                break;
                            case PlaniDataFlags.Planityp:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.String).Value = planityp;
                                break;
                            case PlaniDataFlags.Sys:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = sys;
                                break;
                            case PlaniDataFlags.TimeStamp:
                                cmd.Parameters.Add(cols[flag], MySqlDbType.UInt32).Value = timeStamp;
                                break;
                        }
                    }
                }
                cmd.Parameters.Add("id", MySqlDbType.UInt32);
                cmd.Prepare();
                foreach (uint id in ids) {
                    cmd.Parameters["id"].Value = id;
                    cmd.ExecuteNonQuery();
                }
            }
        }
        public override PlaniUpdater Update() {
            throw new InvalidOperationException();
        }
    }
}
namespace IWDB.Parser {
	static class IWCache {
		public static System.Xml.XmlNode Query(String url, MySqlConnection con, String DBPrefix) {
			MySqlCommand cmd = new MySqlCommand("SELECT data FROM " + DBPrefix + "iw_cache WHERE url=?url", con);
			cmd.Parameters.Add("?url", MySqlDbType.VarChar).Value = url;
			cmd.Prepare();
			object res = cmd.ExecuteScalar();
			String str = null;
			if (res == null) {
				str = Load(url, con, DBPrefix);
			} else {
				str = (String)res;
			}
			System.Xml.XmlDocument d = new System.Xml.XmlDocument();
			d.LoadXml(str);
			return d;			
		}
		private static String Load(String url, MySqlConnection con, String DBPrefix) {
			WebRequest req = WebRequest.Create(url);
			HttpWebResponse resp = (HttpWebResponse)req.GetResponse();
			Match m = Regex.Match(resp.ContentType, "charset=([^ ]+)");
			StreamReader r = new StreamReader(resp.GetResponseStream(), Encoding.GetEncoding(m.Success ? m.Groups[1].Value : "ISO-8859-1"));
			String data = r.ReadToEnd();
			r.Close();
			MySqlCommand cmd = new MySqlCommand("INSERT INTO "+DBPrefix+"iw_cache (url, data) VALUES (?url, ?data)", con);
			cmd.Parameters.Add("?url", MySqlDbType.VarChar).Value=url;
			cmd.Parameters.Add("?data", MySqlDbType.Text).Value=data;
			cmd.Prepare();
			cmd.ExecuteNonQuery();
			return data;
		}
	}
	[Flags]
	public enum KnownData {
		Owner=1,
		Name=2,
	}
	public class PlaniIDFetcher {
		MySqlCommand idQry;
		MySqlCommand planiInsert;
		KnownData knownData;
		public PlaniIDFetcher(KnownData knownData, MySqlConnection con, String DBPrefix) {
			idQry = new MySqlCommand(@"SELECT ID FROM " + DBPrefix + @"universum WHERE gala=?gal AND sys=?sys AND pla=?pla", con);
			idQry.Parameters.Add("?gal", MySqlDbType.UInt32);
			idQry.Parameters.Add("?sys", MySqlDbType.UInt32);
			idQry.Parameters.Add("?pla", MySqlDbType.UInt32);
			idQry.Prepare();
			this.knownData = knownData;
			StringBuilder qry;
			if(knownData>0)
				qry = new StringBuilder("INSERT INTO ");
			else
				qry = new StringBuilder("INSERT IGNORE INTO ");
			qry.Append(DBPrefix);
			qry.Append("universum (gala, sys, pla");
			if ((knownData & KnownData.Name) > 0)
				qry.Append(", planiname");
			if ((knownData & KnownData.Owner) > 0)
				qry.Append(", ownername");
			qry.Append(") VALUES (?gal, ?sys, ?pla");
			if ((knownData & KnownData.Name) > 0)
				qry.Append(", ?name");
			if ((knownData & KnownData.Owner) > 0)
				qry.Append(", ?owner");
			if (knownData == 0) {
				qry.Append(")");
			} else {
				qry.Append(") ON DUPLICATE KEY UPDATE ");
				if ((knownData & KnownData.Name) > 0)
					qry.Append("planiname=IFNULL(VALUES(planiname), planiname), ");
				if ((knownData & KnownData.Owner) > 0)
					qry.Append("ownername=IFNULL(VALUES(ownername), ownername), ");
				qry.Length -= 2;
			}
			planiInsert = new MySqlCommand(qry.ToString(), con);
			planiInsert.Parameters.Add("?gal", MySqlDbType.UInt32);
			planiInsert.Parameters.Add("?sys", MySqlDbType.UInt32);
			planiInsert.Parameters.Add("?pla", MySqlDbType.UInt32);
			if ((knownData & KnownData.Name) > 0)
				planiInsert.Parameters.Add("?name", MySqlDbType.String);
			if ((knownData & KnownData.Owner) > 0)
				planiInsert.Parameters.Add("?owner", MySqlDbType.String);
			planiInsert.Prepare();
		}
		public uint GetID(uint gala, uint sys, uint pla) {
			if (knownData > 0)
				throw new InvalidOperationException();
			return GetID(gala, sys, pla, null, null);
		}
		public uint GetID(uint gala, uint sys, uint pla, String planiName) {
			if ((knownData & KnownData.Owner) > 0)
				throw new InvalidOperationException();
			return GetID(gala, sys, pla, planiName, null);
		}
		public uint GetID(uint gala, uint sys, uint pla, String planiName, String planiOwner) {
			planiInsert.Parameters["?gal"].Value = gala;
			planiInsert.Parameters["?sys"].Value = sys;
			planiInsert.Parameters["?pla"].Value = pla;
			if ((knownData & KnownData.Name) > 0)
				planiInsert.Parameters["?name"].Value = planiName;
			if ((knownData & KnownData.Owner) > 0)
				planiInsert.Parameters["?owner"].Value = planiOwner;
			planiInsert.ExecuteNonQuery();
			long lastInsID = planiInsert.LastInsertedId;
			if (lastInsID != 0) {
				return Convert.ToUInt32(lastInsID);
			} else {
				idQry.Parameters["?gal"].Value = gala;
				idQry.Parameters["?sys"].Value = sys;
				idQry.Parameters["?pla"].Value = pla;
				object ret = idQry.ExecuteScalar();
				return Convert.ToUInt32(ret);
			}
		}
	}
}
