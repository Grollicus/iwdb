using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace AreaZeusRechner {
    //TODO: Modifikatoren einbauen
    //TODO: weiterrechnen bis Tag X
    //TODO: 
    public partial class Form1 : Form {
        public Form1() {
            InitializeComponent();
        }

        class PrsException : Exception {
            public PrsException(string s, Exception inner) : base(s, inner) { }
        }
        private double Prs(TextBox tb) {
            try {
                return double.Parse(tb.Text, System.Globalization.NumberStyles.Any);
            } catch (FormatException e) {
                throw new PrsException(tb.Name, e);
            }
        }

        private void button1_Click(object sender, EventArgs e) {
            try {
                ResourceSet initialProduction = new ResourceSet() {
                    Eisen = Prs(TbInitialFe),
                    Stahl = Prs(TbInitialSt),
                    VV4A = Prs(TbInitialVV),
                    Chemie = Prs(TbInitialCh),
                    Eis = Prs(TbInitialEi),
                    Wasser = Prs(TbInitialWa),
                    Energie = Prs(TbInitialEn),
                    FP = Prs(TbInitialFP)
                };
                ResourceSet backgroundProduction = new ResourceSet() {
                    Eisen = Prs(TbBgFe),
                    Stahl = Prs(TbBgSt),
                    VV4A = Prs(TbBgVV),
                    Chemie = Prs(TbBgCh),
                    Eis = Prs(TbBgEi),
                    Wasser = Prs(TbBgWa),
                    Energie = Prs(TbBgEn),
                };
                ResourceSet backgroundProductionForP = new ResourceSet() {
                    Eisen = Prs(TbBgForFE),
                    Stahl = Prs(TbBgForSt),
                    VV4A = Prs(TbBgForVV),
                    Chemie = Prs(TbBgForCh),
                    Eis = Prs(TbBgForEi),
                    Wasser = Prs(TbBgForWa),
                    Energie = Prs(TbBgForEn),
                };
                StringBuilder output = new StringBuilder("[pre]");
                output.AppendLine("areaSim");
                AreaZeusSim areaSim = new AreaZeusSim(Prs(TbKostenAreaArea), Prs(TbKostenAreaZeus), Prs(TbKostenAreaGenV2), Prs(TbZeusPlanis), initialProduction, backgroundProduction, backgroundProductionForP);
                areaSim.ResearchAreas();
                areaSim.ResearchZeus();
                areaSim.ResearchGenetik();
                areaSim.LogSummary();
                areaSim.SimulateToDay(Prs(TbSimUntil));
                areaSim.LogSummary();
                output.AppendLine(areaSim.Output);
                output.Append("[/pre][hr][pre]");

                output.AppendLine("zeusSim");
                AreaZeusSim zeusSim = new AreaZeusSim(Prs(TbKostenZeusArea), Prs(TbKostenZeusZeus), Prs(TbKostenZeusGenV2), Prs(TbZeusPlanis), initialProduction, backgroundProduction, backgroundProductionForP);
                zeusSim.ResearchZeus();
                zeusSim.ResearchAreas();
                zeusSim.ResearchGenetik();
                zeusSim.LogSummary();
                output.AppendLine(zeusSim.Output);
                output.Append("[/pre][hr][pre]");

                output.AppendLine("orb80Sim");
                AreaZeusSim orb80Sim = new AreaZeusSim(Prs(TbKostenZeusArea), Prs(TbKostenZeusZeus), Prs(TbKostenZeusGenV2), Prs(TbZeusPlanis), initialProduction, backgroundProduction, backgroundProductionForP);
                orb80Sim.Build80OrbFlabs();
                orb80Sim.ResearchZeus();
                orb80Sim.ResearchAreas();
                orb80Sim.ResearchGenetik();
                orb80Sim.LogSummary();
                orb80Sim.SimulateToDay(Prs(TbSimUntil));
                orb80Sim.LogSummary();
                output.Append(orb80Sim.Output);
                output.Append("[/pre][hr][pre]");

                output.AppendLine("area40Sim");
                AreaZeusSim area40Sim = new AreaZeusSim(Prs(TbKostenAreaArea), Prs(TbKostenAreaZeus), Prs(TbKostenAreaGenV2), Prs(TbZeusPlanis), initialProduction, backgroundProduction, backgroundProductionForP);
                area40Sim.ResearchAreas(40);
                area40Sim.ResearchZeus();
                area40Sim.ResearchGenetik();
                area40Sim.LogSummary();
                area40Sim.SimulateToDay(Prs(TbSimUntil));
                area40Sim.LogSummary();
                output.AppendLine(area40Sim.Output);
                output.AppendLine("[/pre]"); 

                tbOutp.Text = output.ToString();

            } catch (PrsException ex) {
                tbOutp.Text = "FEHLER: " + ex.ToString()+ Environment.NewLine + ex.InnerException.ToString();
            }
        }

        private void Form1_FormClosed(object sender, FormClosedEventArgs e) {
            AreaZeusRechner.Properties.Settings.Default.Save();
        }

        private void tbOutp_KeyDown(object sender, KeyEventArgs e) {
            if (e.Control && e.KeyCode == Keys.A) {
                tbOutp.SelectAll();
                e.SuppressKeyPress = true;
            }
        }

        private void listBox1_MouseDown(object sender, MouseEventArgs e) {
            int index = listBox1.IndexFromPoint(e.X, e.Y);
            if (index >= 0) {
                listBox1.DoDragDrop(listBox1.Items[index].ToString(), DragDropEffects.Move);
            }
        }

        private void listBox1_DragOver(object sender, DragEventArgs e) {
            if (e.Data.GetDataPresent("Text"))
                e.Effect = DragDropEffects.Move;
        }

        private void listBox1_DragDrop(object sender, DragEventArgs e) {
            String val = e.Data.GetData(typeof(string)) as string;
            Point p = PointToClient(new Point(e.X, e.Y));
            int ind = listBox1.IndexFromPoint(p);
            if (ind < 0)
                ind = listBox1.Items.Count - 1;
            listBox1.Items.Remove(val);
            listBox1.Items.Insert(ind, val);
        }
    }

    class AreaZeusSim:LazySim {
        double areaCost;
        double zeusCost;
        double genetikCost;
        double zeusPlanis;
        ResourceSet bg;
        ResourceSet bgForP;
        StringBuilder output = new StringBuilder();

        public AreaZeusSim(double AreaCostTsd, double ZeusCostTsd, double GenetikCostTsd, double ZeusPlanis, ResourceSet initialProduction, ResourceSet backgroundProduction, ResourceSet backgroundProductionForP) {
            this.areaCost = AreaCostTsd*1000;
            this.zeusCost = ZeusCostTsd*1000;
            this.genetikCost = GenetikCostTsd*1000;
            this.zeusPlanis=ZeusPlanis;
            this.currentState.CurrentProduction = initialProduction;
            bg = backgroundProduction + backgroundProductionForP;
            bgForP = backgroundProductionForP;
        }

        public void ResearchAreas(int toBuild = 50) {
            this.SelectResearch("AreaFor");
            this.CompleteResearch();
            Log("Areas erforscht");
            for (int i = 0; i < toBuild; ++i)
                this.BuildGeb("Area");
            Log("Areas gebaut");
        }
        public void ResearchZeus() {
            this.SelectResearch("ZeusFor");
            this.CompleteResearch();
            Log("Zeus erforscht");
            this.BuildGeb("GrWerft");
            this.BuildGeb("GrWerft");
            Log("Werften gebaut");
        }
        public void ResearchGenetik() {
            this.SelectResearch("GenetikFor");
            this.CompleteResearch();
            Log("Genetik erforscht");
        }
        public void Build80OrbFlabs() {
            int i = 45;
            for (; i < 65; ++i)
                this.BuildGeb("orbFlab");
            for (; i < 80; ++i)
                this.BuildGeb("orbFlab2");
            Log("80 orb Flabs gebaut");
        }
        public void LogSummary() {
            Log("Produktion /h: " + currentState.CurrentProduction.ToString());
            Log("Ressbilanz: " + currentState.CurrentStock.ToString());
            StringBuilder gebs = currentState.Buildings.Aggregate(new StringBuilder(), (sb, p) => sb.Append(p.Value).Append("x ").Append(p.Key).Append(", "));
            if (gebs.Length > 2)
                gebs.Length -= 2;
            Log("Gebs gebaut:  " + gebs.ToString());
        }
        public String Output { get { return output.ToString(); } }

        protected void Log(String s) {
            output.Append(this.currentState.SimZeit);
            output.Append(' ');
            output.AppendLine(s);
        }

        protected override ResourceSet Cost(String geb) {
            switch (geb) {
                case "Area":
                    return new ResourceSet() { Zeit = TimeSpan.FromHours(5.4) };
                case "Zeus":
                    return new ResourceSet();
                case "orbFlab":
                    return new ResourceSet() { Zeit = TimeSpan.FromHours(1.4 * 6 * 0.9) };
                case "orbFlab2":
                    return new ResourceSet() { Zeit = TimeSpan.FromHours(1.8 * 6 * 0.9) };
                case "GrWerft":
                    return new ResourceSet() { Zeit = TimeSpan.FromDays(2 * 0.9) };
                case "AreaFor":
                    return new ResourceSet() { FP = areaCost };
                case "ZeusFor":
                    return new ResourceSet() { FP = zeusCost };
                case "GenetikFor":
                    return new ResourceSet() { FP = genetikCost };
                default:
                    throw new InvalidOperationException("Unbekanntes Mopped: " + geb);
            }
        }
        protected override ResourceSet Production(String geb) {

            switch (geb) {
                case "Area":
                    return new ResourceSet() { FP = 262.7, Chemie = -50, Energie = -380 } - bgForP * Cost(geb).Zeit.TotalHours;
                case "orbFlab":
                    return new ResourceSet() { FP = 142, Chemie = -15, Energie = -150 } - bgForP * Cost(geb).Zeit.TotalHours;
                case "orbFlab2":
                    return new ResourceSet() { FP = 142, Chemie = -15, Energie = -150 } - bgForP * Cost(geb).Zeit.TotalHours;
                case "GrWerft":
                    return new ResourceSet() { Zeus = zeusPlanis / (35 * 1.2) } - bgForP * Cost(geb).Zeit.TotalHours;
                default:
                    throw new InvalidOperationException("Unbekanntes Mopped: " + geb);
            }
        }
        protected override ResourceSet BackgroundProduction {
            get { return bg; }
        }
    }

    class SimState : ResourceSet {
        public ResourceSet CurrentStock = new ResourceSet();
        public ResourceSet CurrentProduction = new ResourceSet();
        public DictionaryWithDefault<String, uint> Buildings = new DictionaryWithDefault<String, uint>() { DefaultValue = 0 };
        public TimeSpan SimZeit = TimeSpan.Zero;
    }

    abstract class LazySim {
        protected SimState currentState = new SimState() { };

        public void SelectResearch(String name) {
            currentState.CurrentStock -= Cost(name);
        }
        public void BuildGeb(String name) {
            ResourceSet gebCost = Cost(name);
            currentState.CurrentStock -= gebCost;
            Simulate(gebCost.Zeit);
            currentState.CurrentProduction += Production(name);
            currentState.Buildings[name]++;
        }
        public void CompleteResearch() {
            if (currentState.CurrentStock.FP < 0) {
                Simulate(TimeSpan.FromHours((-1)*currentState.CurrentStock.FP / currentState.CurrentProduction.FP));
            }
        }
        protected void Simulate(TimeSpan time) {
            System.Diagnostics.Debug.Assert(time.Ticks > 0);
            currentState.CurrentStock += currentState.CurrentProduction * time.TotalHours;
            currentState.SimZeit += time;

            currentState.CurrentProduction += BackgroundProduction * time.TotalHours;
            currentState.CurrentStock += BackgroundProduction * (time.TotalHours * time.TotalHours * 0.5); // Integral beachten!
        }
        public void SimulateToDay(double d) {
            double days = d - currentState.SimZeit.TotalDays;
            if (days <= 0)
                return;
            Simulate(TimeSpan.FromDays(days));
        }
        protected abstract ResourceSet Cost(String s);
        protected abstract ResourceSet Production(String s);
        protected abstract ResourceSet BackgroundProduction { get; }
    }

    //From: IWDB Parser, modified
    class ResourceSet {
        public double Eisen;
        public double Stahl;
        public double Chemie;
        public double VV4A;
        public double Eis;
        public double Wasser;
        public double Energie;
        public double Credits;
        public double Bev;
        public double FP;
        public double Zeus;
        public TimeSpan Zeit;

        public ResourceSet() {
            Eisen = Stahl = Chemie = VV4A = Eis = Wasser = Energie = Credits = Bev = FP=  Zeus = 0;
            Zeit = TimeSpan.Zero;
        }
        public static ResourceSet operator -(ResourceSet rs1, ResourceSet rs2) {
            ResourceSet ret = new ResourceSet();
            ret.Eisen = rs1.Eisen - rs2.Eisen;
            ret.Stahl = rs1.Stahl - rs2.Stahl;
            ret.Chemie = rs1.Chemie - rs2.Chemie;
            ret.VV4A = rs1.VV4A - rs2.VV4A;
            ret.Eis = rs1.Eis - rs2.Eis;
            ret.Wasser = rs1.Wasser - rs2.Wasser;
            ret.Energie = rs1.Energie - rs2.Energie;
            ret.Credits = rs1.Credits - rs2.Credits;
            ret.Bev = rs1.Bev - rs2.Bev;
            ret.FP = rs1.FP - rs2.FP;
            ret.Zeit = rs1.Zeit - rs2.Zeit;
            ret.Zeus = rs1.Zeus - rs2.Zeus;
            return ret;
        }
        public static ResourceSet operator +(ResourceSet rs1, ResourceSet rs2) {
            ResourceSet ret = new ResourceSet();
            ret.Eisen = rs1.Eisen + rs2.Eisen;
            ret.Stahl = rs1.Stahl + rs2.Stahl;
            ret.Chemie = rs1.Chemie + rs2.Chemie;
            ret.VV4A = rs1.VV4A + rs2.VV4A;
            ret.Eis = rs1.Eis + rs2.Eis;
            ret.Wasser = rs1.Wasser + rs2.Wasser;
            ret.Energie = rs1.Energie + rs2.Energie;
            ret.Credits = rs1.Credits + rs2.Credits;
            ret.Bev = rs1.Bev + rs2.Bev;
            ret.FP = rs1.FP + rs2.FP;
            ret.Zeit = rs1.Zeit + rs2.Zeit;
            ret.Zeus = rs1.Zeus + rs2.Zeus;
            return ret;
        }
        public static ResourceSet operator *(ResourceSet rs1, double scalar) {
            ResourceSet ret = new ResourceSet();
            ret.Eisen = rs1.Eisen * scalar;
            ret.Stahl = rs1.Stahl * scalar;
            ret.Chemie = rs1.Chemie * scalar;
            ret.VV4A = rs1.VV4A * scalar;
            ret.Eis = rs1.Eis * scalar;
            ret.Wasser = rs1.Wasser * scalar;
            ret.Energie = rs1.Energie * scalar;
            ret.Credits = rs1.Credits * scalar;
            ret.Bev = rs1.Bev * scalar;
            ret.FP = rs1.FP * scalar;
            ret.Zeus = rs1.Zeus * scalar;
            ret.Zeit = TimeSpan.FromSeconds(rs1.Zeit.TotalSeconds * scalar);
            return ret;
        }
        public static ResourceSet operator *(double scalar, ResourceSet rs2) {
            return rs2 * scalar;
        }
        public void Set(String name, String value) {
            switch (name) {
                case "Eisen":
                    this.Eisen = double.Parse(value);
                    break;
                case "Stahl":
                case "Produktionskapazität für Stahl":
                    this.Stahl = double.Parse(value);
                    break;
                case "Wasser":
                case "Produktionskapazität für Wasser":
                    this.Wasser = double.Parse(value);
                    break;
                case "Energie":
                    this.Energie = double.Parse(value);
                    break;
                case "chem. Elemente":
                    this.Chemie = double.Parse(value);
                    break;
                case "Bevölkerung":
                    this.Bev = double.Parse(value);
                    break;
                case "Eis":
                    this.Eis = double.Parse(value);
                    break;
                case "Credits":
                case "Zufriedenheit":
                    this.Credits = double.Parse(value);
                    break;
                case "VV4A":
                case "Produktionskapazität für VV4A":
                    this.VV4A = double.Parse(value);
                    break;
                case "Forschungspunkte":
                    this.FP = double.Parse(value);
                    break;
                default:
                    break;
            }
        }
        public void Set(int num, String value) {
            Set(num, double.Parse(value));
        }
        public void Set(int num, double val) {
            switch (num) {
                case 0:
                    Eisen = val;
                    break;
                case 1:
                    Stahl = val;
                    break;
                case 2:
                    VV4A = val;
                    break;
                case 3:
                    Chemie = val;
                    break;
                case 4:
                    Eis = val;
                    break;
                case 5:
                    Wasser = val;
                    break;
                case 6:
                    Energie = val;
                    break;
            }
        }
        public void SetIWID(int num, String value) {
            double val = double.Parse(value);
            switch (num) {
                case 1:
                    Eisen = val;
                    break;
                case 2:
                    Stahl = val;
                    break;
                case 3:
                    VV4A = val;
                    break;
                case 5:
                    Chemie = val;
                    break;
                case 4:
                    Eis = val;
                    break;
                case 6:
                    Wasser = val;
                    break;
                case 7:
                    Energie = val;
                    break;
            }
        }
        public void ParseXml(System.Xml.XmlNode ressourcenXml) {
            foreach (System.Xml.XmlNode n in ressourcenXml.SelectNodes("ressource")) {
                int id = int.Parse(n.SelectSingleNode("id").InnerText);
                SetIWID(id, n.SelectSingleNode("anzahl").InnerText);
            }
        }
        public void ParseXmlKb(System.Xml.XmlNode ressourcenXml) {
            foreach (System.Xml.XmlNode n in ressourcenXml.SelectNodes("resource")) {
                int id = int.Parse(n.SelectSingleNode("id").Attributes["value"].InnerText);
                SetIWID(id, n.SelectSingleNode("anzahl").Attributes["value"].InnerText);
            }
        }
        public double RaidScore { get { return Eisen * 1 + Stahl * 2 + Chemie * 1.5f + VV4A * 4 + Eis * 2 + Wasser * 4 + Energie; } }
        public override string ToString() {
            StringBuilder sb = new StringBuilder();
            sb.AppendFormat("Eisen={0:#,0.##} ", Eisen);
            sb.AppendFormat("Stahl={0:#,0.##} ", Stahl);
            sb.AppendFormat("VV4A={0:#,0.##} ", VV4A);
            sb.AppendFormat("Chemie={0:#,0.##} ", Chemie);
            sb.AppendFormat("Eis={0:#,0.##} ", Eis);
            sb.AppendFormat("Wasser={0:#,0.##} ", Wasser);
            sb.AppendFormat("Energie={0:#,0.##} ", Energie);
            sb.AppendFormat("FP={0:#,0.##} ", FP);
            sb.AppendFormat("Zeus={0:#,0.##}", Zeus);
            return sb.ToString();
        }
    }

    //from: http://stackoverflow.com/questions/2601477/dictionary-returning-a-default-value-if-the-key-does-not-exist
    public class DictionaryWithDefault<TKey, TValue> : Dictionary<TKey, TValue> {
        TValue _default;
        public TValue DefaultValue {
            get { return _default; }
            set { _default = value; }
        }
        public DictionaryWithDefault() : base() { }
        public DictionaryWithDefault(TValue defaultValue)
            : base() {
            _default = defaultValue;
        }
        public new TValue this[TKey key] {
            get { TValue ret; return base.TryGetValue(key, out ret) ? ret : _default; }
            set { base[key] = value; }
        }
    }

}
