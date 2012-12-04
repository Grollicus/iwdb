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
            IWDBTest.DoTest();
        }
    }
}
