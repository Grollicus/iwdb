using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using MySql.Data.MySqlClient;

namespace IWDB {
    public class IWDBTest {
        public static void DoTest() {
            
#if DEBUG
            MySqlConnection con = new MySqlConnection("Host=10.1.0.20;Username=Taddi;Password=aabc;Database=iwdingends");
#else
            MySqlConnection con = null;
#endif
            con.Open();
            IWDB.Parser.BesonderheitenData besDta = new IWDB.Parser.BesonderheitenData(con, "iwdb_");
            PlaniFetcher pinfo = new PlaniFetcher(besDta, con, "iwdb_");
            pinfo.Ownername = "Xardas";
            List<PlaniInfo> res = pinfo.FetchMatching(PlaniDataFlags.AllUni);
            PlaniUpdater upd = new PlaniUpdater(pinfo);
            upd.Planiname = "test!";
            upd.Save();
            List<PlaniInfo> res2 = pinfo.FetchMatching(PlaniDataFlags.AllUni);
            //TODO: PlaniUpdater testen!!
        }
    }
}
