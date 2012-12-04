using System;
using System.Collections.Generic;
using System.Text;
using IWDB;
using Flow;
using System.Diagnostics;

namespace Test {
    static class Blub {
        public static void Main(String[] args) {
            MaximumFlowNetwork.Test();
            MinimumFlowNetwork.Test();
            IWDB.Parser.FlugRechner.Test();
            TestTimeConv();
            IWDBTest.DoTest();
        }

        private static void TestTimeConv() {
            TimeSpan ts = IWDB.IWDBUtils.parseIWZeitspanne("220 Tage 01:10:03");
            Debug.Assert(ts.Days == 220 && ts.Hours == 1 && ts.Minutes == 10 && ts.Seconds == 3);
            DateTime dt = IWDB.IWDBUtils.fromUnixTimestamp(IWDB.IWDBUtils.parsePreciseIWTime("4.12.2012 17:52:05"));
            Debug.Assert(dt.Day == 4 && dt.Month == 12 && dt.Year == 2012 && dt.Hour == 17 && dt.Minute == 52 && dt.Second == 5);
            DateTime dt2 = IWDB.IWDBUtils.fromUnixTimestamp(IWDB.IWDBUtils.parsePreciseIWTime("4.1.2000 1:2:7"));
            Debug.Assert(dt2.Day == 4 && dt2.Month == 1 && dt2.Year == 2000 && dt2.Hour == 1 && dt2.Minute == 2 && dt2.Second == 7);

            //DateTime rs2 = IWDB.IWDBUtils.fromUnixTimestamp(1354644131) - IWDB.IWDBUtils.parseIWZeitspanne("220 Tage 02:20:09"); // Rundenstart
        }
    }
}
