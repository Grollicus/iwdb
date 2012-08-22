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
            StringBuilder sb = new StringBuilder(cbHandler.Text);
            sb.Append('\0');
            switch (cbHandler.Text) {
                case "newscan":
                    progressBar1.Value = 0;
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
                    break;
                case "buildingqueue":
                    sb.Append(TbCoords.Text).Append('\0');
                    sb.Append(cbBauschleifenTyp.Text).Append('\0');
                    sb.Append(tbScan.Text.Replace("\r\n", "\n").Replace("\r", "\n").Replace(" \t", " ").Replace("\t", " "));
                    break;
            }
            sb.Append("\0\0");
            backgroundWorker1.RunWorkerAsync(new Tuple<string, uint, string>(sb.ToString(), count, cbHandler.Text));
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

        private void BtPerfTest_Click(object sender, EventArgs e) {
            if (backgroundWorker1.IsBusy) {
                backgroundWorker1.CancelAsync();
                return;
            }
            Go(100);
            BtPerfTest.Text = "Stop";
            BtGo.Text = " - ";
            progressBar1.Maximum = 100;
        }

        private String FormatResponse(String resp, String handler) {
            switch (handler) {
                case "buildingqueue":
                    return resp.Split('\n').Where(s=> s.Trim().Length > 0).Select(l => IWDB.IWDBUtils.fromUnixTimestamp(uint.Parse(l)).ToString()).Aggregate(new StringBuilder(), (sb, s) => sb.AppendLine(s)).ToString();
                default:
                    return resp;
            }
        }

        private void backgroundWorker1_DoWork(object sender, DoWorkEventArgs e) {
            try {
                Tuple<string, uint, string> req = (Tuple<string, uint, string>)e.Argument;
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
                    backgroundWorker1.ReportProgress((int)((100 * i) / req.Item2), FormatResponse(r.ReadToEnd(), req.Item3));
                }
            }
            catch (Exception ex) {
                e.Result = ex.ToString();
                return;
            }
        }

        private void backgroundWorker1_RunWorkerCompleted(object sender, RunWorkerCompletedEventArgs e) {
            BtGo.Text = "Go";
            BtPerfTest.Text = "PerfTest";
            progressBar1.Value = progressBar1.Maximum;
        }

        private void backgroundWorker1_ProgressChanged(object sender, ProgressChangedEventArgs e) {
            tbResp.Text = ((String)e.UserState).Replace("\n", Environment.NewLine);
            progressBar1.Value = e.ProgressPercentage;
        }


        private void cbHandler_SelectedIndexChanged(object sender, EventArgs e) {
            switch(cbHandler.Text) {
                case "newscan":
                    tbID.Visible = TbUID.Visible = cbWar.Visible = cbRestricted.Visible = cbUserAgent.Visible = label1.Visible = label2.Visible = label3.Visible = true;
                    TbCoords.Visible = label5.Visible = label6.Visible = cbBauschleifenTyp.Visible = false;
                    break;
                case "buildingqueue":
                    tbID.Visible = TbUID.Visible = cbWar.Visible = cbRestricted.Visible = cbUserAgent.Visible = label1.Visible = label2.Visible = label3.Visible = false;
                    TbCoords.Visible = label5.Visible = label6.Visible = cbBauschleifenTyp.Visible = true;
                    break;
            }
        }

        private void Form1_FormClosed(object sender, FormClosedEventArgs e) {
            Tester.Properties.Settings.Default.Save();
        }

        private void Form1_Load(object sender, EventArgs e) {
            cbHandler.SelectedIndex = cbHandler.SelectedIndex;
        }
    }
}
