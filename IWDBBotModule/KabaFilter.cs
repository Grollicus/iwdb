using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IWDB.Parser;
using MySql.Data.MySqlClient;
using IRCeX;

namespace IWDB {
	class KabaFilter:RequestHandler {
		List<Tuple<uint?, uint?, uint?, String, String>> filters = new List<Tuple<uint?, uint?, uint?, string, string>>();
		String DBPrefix;
		MySqlConnection con;

		public KabaFilter(String DBPrefix, MySqlConnection con) {
			this.DBPrefix = DBPrefix;
			this.con = con;
			UpdateFilters();
		}

		public void HandleRequest(ParserRequestMessage msg) {
			try {
				con.Open();
				UpdateFilters();
			} finally {
				con.Clone();
			}
		}

		void UpdateFilters() {
			MySqlCommand cmd = new MySqlCommand("SELECT gala, sys, pla, ownerName, ownerAlly FROM " + DBPrefix + "kabafilter", con);
			MySqlDataReader r = cmd.ExecuteReader();
			filters.Clear();
			try {
				while(r.Read()) {
					filters.Add(new Tuple<uint?, uint?, uint?, String, String>(
						r.IsDBNull(0) ? null : (uint?)(r.GetUInt32(0)),
						r.IsDBNull(1) ? null : (uint?)(r.GetUInt32(1)),
						r.IsDBNull(2) ? null : (uint?)(r.GetUInt32(2)),
						r.IsDBNull(3) ? null : r.GetString(3),
						r.IsDBNull(4) ? null : r.GetString(4))
					);
				}
			} finally {
				r.Close();
			}
			Log.WriteLine(LogLevel.E_DEBUG, "Updated Kaba Filters:");
			foreach(Tuple<uint?, uint?, uint?, String, String> el in filters) {
				Log.WriteLine(LogLevel.E_DEBUG, String.Format("Filter: {0} {1} {2} {3} {4} {5}", el.Item1, el.Item2, el.Item3, el.Item4, el.Item5));
			}
		}

		public bool ApplyFilter(uint gala, uint sys, uint pla, String ownerName, String ally) {
			Log.WriteLine(LogLevel.E_DEBUG, String.Format("ApplyFilter: {0} {1} {2} {3} {4} {5}", gala, sys, pla, ownerName, ally));
			foreach(Tuple<uint?, uint?, uint?, String, String> el in filters) {
				if((el.Item1 == null || el.Item2 == gala) && (el.Item2 == null || el.Item2 == sys) && (el.Item3 == null || el.Item3 == pla) && (el.Item4 == null || el.Item4 == ownerName) && (el.Item5 == null || el.Item5 == ally))
					Log.WriteLine(LogLevel.E_DEBUG, "Accepted Kaba");
					return true;
			}
			Log.WriteLine(LogLevel.E_DEBUG, "Rejected Kaba");
			return false;
		}

		public string Name {
			get { return "KabaFilter"; }
		}
	}
}
