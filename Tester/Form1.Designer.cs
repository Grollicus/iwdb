namespace Tester {
    partial class Form1 {
        /// <summary>
        /// Erforderliche Designervariable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Verwendete Ressourcen bereinigen.
        /// </summary>
        /// <param name="disposing">True, wenn verwaltete Ressourcen gelöscht werden sollen; andernfalls False.</param>
        protected override void Dispose(bool disposing) {
            if (disposing && (components != null)) {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Vom Windows Form-Designer generierter Code

        /// <summary>
        /// Erforderliche Methode für die Designerunterstützung.
        /// Der Inhalt der Methode darf nicht mit dem Code-Editor geändert werden.
        /// </summary>
        private void InitializeComponent() {
            this.BtGo = new System.Windows.Forms.Button();
            this.splitContainer1 = new System.Windows.Forms.SplitContainer();
            this.tbScan = new System.Windows.Forms.TextBox();
            this.tbResp = new System.Windows.Forms.TextBox();
            this.label1 = new System.Windows.Forms.Label();
            this.tbID = new System.Windows.Forms.TextBox();
            this.TbUID = new System.Windows.Forms.TextBox();
            this.label2 = new System.Windows.Forms.Label();
            this.cbUserAgent = new System.Windows.Forms.ComboBox();
            this.label3 = new System.Windows.Forms.Label();
            this.cbWar = new System.Windows.Forms.CheckBox();
            this.cbRestricted = new System.Windows.Forms.CheckBox();
            this.backgroundWorker1 = new System.ComponentModel.BackgroundWorker();
            this.label4 = new System.Windows.Forms.Label();
            this.cbHandler = new System.Windows.Forms.ComboBox();
            this.BtPerfTest = new System.Windows.Forms.Button();
            this.progressBar1 = new System.Windows.Forms.ProgressBar();
            ((System.ComponentModel.ISupportInitialize)(this.splitContainer1)).BeginInit();
            this.splitContainer1.Panel1.SuspendLayout();
            this.splitContainer1.Panel2.SuspendLayout();
            this.splitContainer1.SuspendLayout();
            this.SuspendLayout();
            // 
            // BtGo
            // 
            this.BtGo.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.BtGo.Location = new System.Drawing.Point(197, 227);
            this.BtGo.Name = "BtGo";
            this.BtGo.Size = new System.Drawing.Size(75, 23);
            this.BtGo.TabIndex = 0;
            this.BtGo.Text = "Go";
            this.BtGo.UseVisualStyleBackColor = true;
            this.BtGo.Click += new System.EventHandler(this.BtGo_Click);
            // 
            // splitContainer1
            // 
            this.splitContainer1.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom)
                        | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.splitContainer1.Location = new System.Drawing.Point(12, 65);
            this.splitContainer1.Name = "splitContainer1";
            this.splitContainer1.Orientation = System.Windows.Forms.Orientation.Horizontal;
            // 
            // splitContainer1.Panel1
            // 
            this.splitContainer1.Panel1.Controls.Add(this.tbScan);
            // 
            // splitContainer1.Panel2
            // 
            this.splitContainer1.Panel2.Controls.Add(this.tbResp);
            this.splitContainer1.Size = new System.Drawing.Size(260, 156);
            this.splitContainer1.SplitterDistance = 78;
            this.splitContainer1.TabIndex = 1;
            // 
            // tbScan
            // 
            this.tbScan.Dock = System.Windows.Forms.DockStyle.Fill;
            this.tbScan.Location = new System.Drawing.Point(0, 0);
            this.tbScan.Multiline = true;
            this.tbScan.Name = "tbScan";
            this.tbScan.Size = new System.Drawing.Size(260, 78);
            this.tbScan.TabIndex = 0;
            // 
            // tbResp
            // 
            this.tbResp.Dock = System.Windows.Forms.DockStyle.Fill;
            this.tbResp.Location = new System.Drawing.Point(0, 0);
            this.tbResp.Multiline = true;
            this.tbResp.Name = "tbResp";
            this.tbResp.ReadOnly = true;
            this.tbResp.Size = new System.Drawing.Size(260, 74);
            this.tbResp.TabIndex = 1;
            // 
            // label1
            // 
            this.label1.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(137, 15);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(18, 13);
            this.label1.TabIndex = 2;
            this.label1.Text = "ID";
            // 
            // tbID
            // 
            this.tbID.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.tbID.Location = new System.Drawing.Point(161, 12);
            this.tbID.Name = "tbID";
            this.tbID.Size = new System.Drawing.Size(34, 20);
            this.tbID.TabIndex = 3;
            // 
            // TbUID
            // 
            this.TbUID.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.TbUID.Location = new System.Drawing.Point(238, 12);
            this.TbUID.Name = "TbUID";
            this.TbUID.Size = new System.Drawing.Size(34, 20);
            this.TbUID.TabIndex = 5;
            // 
            // label2
            // 
            this.label2.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.label2.AutoSize = true;
            this.label2.Location = new System.Drawing.Point(206, 15);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(26, 13);
            this.label2.TabIndex = 4;
            this.label2.Text = "UID";
            // 
            // cbUserAgent
            // 
            this.cbUserAgent.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.cbUserAgent.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.cbUserAgent.FormattingEnabled = true;
            this.cbUserAgent.Items.AddRange(new object[] {
            "Firefox",
            "Opera"});
            this.cbUserAgent.Location = new System.Drawing.Point(181, 38);
            this.cbUserAgent.Name = "cbUserAgent";
            this.cbUserAgent.Size = new System.Drawing.Size(91, 21);
            this.cbUserAgent.TabIndex = 6;
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.Location = new System.Drawing.Point(153, 42);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(22, 13);
            this.label3.TabIndex = 7;
            this.label3.Text = "UA";
            // 
            // cbWar
            // 
            this.cbWar.AutoSize = true;
            this.cbWar.CheckAlign = System.Drawing.ContentAlignment.MiddleRight;
            this.cbWar.Location = new System.Drawing.Point(12, 42);
            this.cbWar.Name = "cbWar";
            this.cbWar.Size = new System.Drawing.Size(46, 17);
            this.cbWar.TabIndex = 9;
            this.cbWar.Text = "War";
            this.cbWar.UseVisualStyleBackColor = true;
            // 
            // cbRestricted
            // 
            this.cbRestricted.AutoSize = true;
            this.cbRestricted.CheckAlign = System.Drawing.ContentAlignment.MiddleRight;
            this.cbRestricted.Location = new System.Drawing.Point(64, 42);
            this.cbRestricted.Name = "cbRestricted";
            this.cbRestricted.Size = new System.Drawing.Size(74, 17);
            this.cbRestricted.TabIndex = 10;
            this.cbRestricted.Text = "Restricted";
            this.cbRestricted.UseVisualStyleBackColor = true;
            // 
            // backgroundWorker1
            // 
            this.backgroundWorker1.WorkerReportsProgress = true;
            this.backgroundWorker1.WorkerSupportsCancellation = true;
            this.backgroundWorker1.DoWork += new System.ComponentModel.DoWorkEventHandler(this.backgroundWorker1_DoWork);
            this.backgroundWorker1.ProgressChanged += new System.ComponentModel.ProgressChangedEventHandler(this.backgroundWorker1_ProgressChanged);
            this.backgroundWorker1.RunWorkerCompleted += new System.ComponentModel.RunWorkerCompletedEventHandler(this.backgroundWorker1_RunWorkerCompleted);
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.Location = new System.Drawing.Point(12, 15);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(15, 13);
            this.label4.TabIndex = 12;
            this.label4.Text = "H";
            // 
            // cbHandler
            // 
            this.cbHandler.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.cbHandler.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.cbHandler.FormattingEnabled = true;
            this.cbHandler.Items.AddRange(new object[] {
            "newscan",
            "WarFilter",
            "buildingqueue",
            "techtreedepth",
            "KabaFilter"});
            this.cbHandler.Location = new System.Drawing.Point(33, 12);
            this.cbHandler.Name = "cbHandler";
            this.cbHandler.Size = new System.Drawing.Size(98, 21);
            this.cbHandler.TabIndex = 11;
            // 
            // BtPerfTest
            // 
            this.BtPerfTest.Location = new System.Drawing.Point(116, 227);
            this.BtPerfTest.Name = "BtPerfTest";
            this.BtPerfTest.Size = new System.Drawing.Size(75, 23);
            this.BtPerfTest.TabIndex = 13;
            this.BtPerfTest.Text = "PerfTest";
            this.BtPerfTest.UseVisualStyleBackColor = true;
            this.BtPerfTest.Click += new System.EventHandler(this.BtPerfTest_Click);
            // 
            // progressBar1
            // 
            this.progressBar1.Location = new System.Drawing.Point(10, 227);
            this.progressBar1.Name = "progressBar1";
            this.progressBar1.Size = new System.Drawing.Size(100, 23);
            this.progressBar1.TabIndex = 14;
            // 
            // Form1
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(284, 262);
            this.Controls.Add(this.progressBar1);
            this.Controls.Add(this.BtPerfTest);
            this.Controls.Add(this.label4);
            this.Controls.Add(this.cbHandler);
            this.Controls.Add(this.cbRestricted);
            this.Controls.Add(this.cbWar);
            this.Controls.Add(this.label3);
            this.Controls.Add(this.cbUserAgent);
            this.Controls.Add(this.TbUID);
            this.Controls.Add(this.label2);
            this.Controls.Add(this.tbID);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.splitContainer1);
            this.Controls.Add(this.BtGo);
            this.Name = "Form1";
            this.Text = "Form1";
            this.Load += new System.EventHandler(this.Form1_Load);
            this.splitContainer1.Panel1.ResumeLayout(false);
            this.splitContainer1.Panel1.PerformLayout();
            this.splitContainer1.Panel2.ResumeLayout(false);
            this.splitContainer1.Panel2.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.splitContainer1)).EndInit();
            this.splitContainer1.ResumeLayout(false);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button BtGo;
        private System.Windows.Forms.SplitContainer splitContainer1;
        private System.Windows.Forms.TextBox tbScan;
        private System.Windows.Forms.TextBox tbResp;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.TextBox tbID;
        private System.Windows.Forms.TextBox TbUID;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.ComboBox cbUserAgent;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.CheckBox cbWar;
        private System.Windows.Forms.CheckBox cbRestricted;
        private System.ComponentModel.BackgroundWorker backgroundWorker1;
        private System.Windows.Forms.Label label4;
        private System.Windows.Forms.ComboBox cbHandler;
        private System.Windows.Forms.Button BtPerfTest;
        private System.Windows.Forms.ProgressBar progressBar1;
    }
}

