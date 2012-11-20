using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Utils;
using System.Text.RegularExpressions;
using System.IO;
using System.Threading;

namespace iwfpoll {
    class Program {
        static void Main(string[] args) {
            while (true) {
                Match m = Re.Match(WebRq.Get("http://www.icewars-forum.de/index.php"), @"Aktive Benutzer in den letzten 5 Minuten:([\s\S]*?)</div>");
                File.AppendAllText("iwf.htm", DateTime.Now.ToString() + ":<br/>" + m.Groups[1].Value+"<br />");
                Console.WriteLine("updated " + DateTime.Now.ToString());
                Thread.Sleep(TimeSpan.FromMinutes(5));
            }
        }
    }
}
