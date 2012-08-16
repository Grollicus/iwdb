using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using IWDB;

namespace Tester {
    public partial class Form1 : Form {
        public Form1() {
            InitializeComponent();
        }

        private void Go(uint count) {
            progressBar1.Value = 0;
            StringBuilder sb = new StringBuilder("newscan\0");
            try {
                sb.Append(int.Parse(tbID.Text));
                sb.Append('\0');
            } catch (Exception ex) {
                tbResp.Text = "ID: " + ex.ToString();
                return;
            }
            try {
                sb.Append(int.Parse(TbUID.Text));
                sb.Append('\0');
            } catch (Exception ex) {
                tbResp.Text = "ID: " + ex.ToString();
                return;
            }
            sb.Append(cbUserAgent.Text); //haha das geht wirklich so
            sb.Append('\0');
            sb.Append(cbWar.Checked ? "1\0" : "0\0");
            sb.Append(cbRestricted.Checked ? "1\0" : "0\0");
            sb.Append(tbScan.Text.Replace("\r\n", "\n").Replace("\r", "\n").Replace(" \t", " ").Replace("\t", " "));
            sb.Append("\0\0");
            backgroundWorker1.RunWorkerAsync(new Pair<string, uint>(sb.ToString(), count));
            BtGo.Text = "Running";
        }

        private void BtGo_Click(object sender, EventArgs e) {
            if (backgroundWorker1.IsBusy) {
                System.Media.SystemSounds.Beep.Play();
                return;
            }
            BtPerfTest.Text = " - ";
            Go(1);
            progressBar1.Maximum = 1;
        }

        private void backgroundWorker1_DoWork(object sender, DoWorkEventArgs e) {
            try {
                Pair<string, uint> req = (Pair<string, uint>)e.Argument;
                for (uint i = 0; i < req.Item2; ++i) {
                    if(e.Cancel)
                        break;
                    System.Net.Sockets.Socket s = new System.Net.Sockets.Socket(System.Net.Sockets.AddressFamily.InterNetwork, System.Net.Sockets.SocketType.Stream, System.Net.Sockets.ProtocolType.Tcp);
                    s.Connect("localhost", 5124);
                    System.Net.Sockets.NetworkStream ns = new System.Net.Sockets.NetworkStream(s);
                    System.IO.StreamWriter sw = new System.IO.StreamWriter(ns);
                    System.IO.StreamReader r = new System.IO.StreamReader(ns);
                    sw.Write(req.Item1);
                    sw.Flush();
                    backgroundWorker1.ReportProgress((int)((100 * i) / req.Item2), r.ReadToEnd());
                }
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
            BtPerfTest.Text = "PerfTest";
            progressBar1.Value = progressBar1.Maximum;
        }

        private void backgroundWorker1_ProgressChanged(object sender, ProgressChangedEventArgs e) {
            tbResp.Text = (String)e.UserState;
            progressBar1.Value = e.ProgressPercentage;
        }

        private void BtPerfTest_Click(object sender, EventArgs e) {
            if (backgroundWorker1.IsBusy) {
                backgroundWorker1.CancelAsync();
            }
            Go(100);
            BtPerfTest.Text = "Stop";
            BtGo.Text = " - ";
            progressBar1.Maximum = 100;
        }
    }
}
