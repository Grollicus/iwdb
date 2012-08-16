using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace Tester {
    public partial class Form1 : Form {
        public Form1() {
            InitializeComponent();
        }

        private void BtGo_Click(object sender, EventArgs e) {
            if (backgroundWorker1.IsBusy) {
                System.Media.SystemSounds.Beep.Play();
                return;
            }
            StringBuilder sb = new StringBuilder("newscan\0");
            try {
                sb.Append(int.Parse(tbID.Text));
                sb.Append('\0');
            }
            catch (Exception ex) {
                tbResp.Text = "ID: " + ex.ToString();
                return;
            }
            try {
                sb.Append(int.Parse(TbUID .Text));
                sb.Append('\0');
            }
            catch (Exception ex) {
                tbResp.Text = "ID: " + ex.ToString();
                return;
            }
            sb.Append(cbUserAgent.Text); //haha das geht wirklich so
            sb.Append('\0');
            sb.Append(cbWar.Checked ? "1\0" : "0\0");
            sb.Append(cbRestricted.Checked ? "1\0" : "0\0");
            sb.Append(tbScan.Text.Replace("\r\n", "\n").Replace("\r", "\n").Replace(" \t", " ").Replace("\t", " "));
            sb.Append("\0\0");
            backgroundWorker1.RunWorkerAsync(sb.ToString());
            BtGo.Text = "Running";
        }

        private void backgroundWorker1_DoWork(object sender, DoWorkEventArgs e) {
            try {
                String req = (String)e.Argument;
                System.Net.Sockets.TcpClient cl = new System.Net.Sockets.TcpClient("localhost", 5124);
                System.Net.Sockets.NetworkStream ns = cl.GetStream();
                System.IO.StreamWriter sw = new System.IO.StreamWriter(ns);
                System.IO.StreamReader r = new System.IO.StreamReader(ns);
                sw.Write(req);
                sw.Flush();
                e.Result = r.ReadToEnd();
            }
            catch (Exception ex) {
                e.Result = ex.ToString();
                return;
            }
        }

        private void Form1_Load(object sender, EventArgs e) {
            cbHandler.SelectedIndex = 0;
            cbUserAgent.SelectedIndex = 0;
            tbID.Text = "2";
            TbUID.Text = "1";
        }

        private void backgroundWorker1_RunWorkerCompleted(object sender, RunWorkerCompletedEventArgs e) {
            BtGo.Text = "Go";
            tbResp.Text = (String)e.Result;
        }
    }
}
