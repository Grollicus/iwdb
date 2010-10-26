using System;
using System.Collections.Generic;
using System.Text;
using MySql.Data.MySqlClient;

namespace IWDB.Parser {
	class TechTreeDepthCalc {
		class Item {
			public readonly uint ID;
			int depth;
			List<Item> reqs;

			public Item(uint id) {
				this.ID = id;
				depth = -1;
				reqs = new List<Item>();
			}
			public void AddReq(Item item) {
				reqs.Add(item);
			}

			public int getDepth() {
				if (depth == -1) {
					depth = 0;
					foreach(Item req in reqs) {
						int reqd = req.getDepth();
						if (reqd >= depth) {
							depth = reqd + 1;
						}
					}
				}
				return depth;
			}
		}
		Dictionary<uint, Item> items;
		String DBPrefix;

		public TechTreeDepthCalc(String DBPrefix) {
			items = new Dictionary<uint, Item>();
			this.DBPrefix = DBPrefix;
		}

		void AddReq(uint itemid, uint reqid) {
			Item item = getItem(itemid);
			Item req = getItem(reqid);
			item.AddReq(req);
		}
		Item getItem(uint itemid) {
			Item ret;
			if (items.TryGetValue(itemid, out ret))
				return ret;
			ret = new Item(itemid);
			items.Add(itemid, ret);
			return ret;
		}

		public void Update(MySqlConnection con) {
			MySqlCommand itemQry = new MySqlCommand(@"SELECT ItemID, RequiresID FROM " + DBPrefix + "techtree_reqs", con);
			MySqlDataReader r = itemQry.ExecuteReader();
			while (r.Read()) {
				AddReq(r.GetUInt32(0), r.GetUInt32(1));
			}
			r.Close();

			

			MySqlCommand update = new MySqlCommand(@"UPDATE " + DBPrefix + "techtree_items SET depth=?depth WHERE ID=?id", con);
			update.Parameters.Add("?depth", MySqlDbType.UInt32);
			update.Parameters.Add("?id", MySqlDbType.UInt32);
			update.Prepare();

			foreach (Item item in items.Values) {
				update.Parameters["?id"].Value = item.ID;
				update.Parameters["?depth"].Value = item.getDepth();
				update.ExecuteNonQuery();
			}
		}
		
	}

	class TechTreeDepthHandler:RequestHandler {
		MySqlConnection con;
		String DBPrefix;
		public TechTreeDepthHandler(MySqlConnection con, String DBPrefix) {
			this.con = con;
			this.DBPrefix = DBPrefix;
		}
#region RequestHandler Member

		public void HandleRequest(ParserRequestMessage msg) {
			TechTreeDepthCalc c = new TechTreeDepthCalc(DBPrefix);
			con.Open();
			c.Update(con);
			con.Close();
            msg.Handled();
        }

		public string Name {
			get { return "techtreedepth"; }
		}

#endregion
	}
}
