using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using System.IO;
using System.Text;
using System.Text.RegularExpressions;
using System.Net;
using System.Diagnostics;
using System.Security.Cryptography.X509Certificates;
using System.Runtime.Serialization.Formatters.Binary;

namespace Utils {
	public class ByteEncoding:Encoding {
		#region implemented abstract members of System.Text.Encoding
		public override int GetByteCount(char[] chars, int index, int count) {
			Check.NotNull(chars, "chars");
			Check.RangeCond(index >= 0, "index < 0!");
            Check.RangeCond(count >= 0, "count < 0!");
			Check.RangeCond(chars.Length > index + count, "chars.Length <= index + count!");
			return count;
		}

		public override int GetBytes(char[] chars, int charIndex, int charCount, byte[] bytes, int byteIndex) {
			Check.NotNull(chars, "chars");
			Check.NotNull(bytes, "bytes");
			Check.RangeCond(charIndex >= 0, "charIndex < 0!");
            Check.RangeCond(charCount >= 0, "charCount < 0!");
            Check.RangeCond(byteIndex >= 0, "byteIndex < 0!");
			Check.RangeCond(chars.Length > charIndex + charCount, "chars.Length <= charIndex+charCount!");
			Check.RangeCond(bytes.Length > byteIndex, "bytes.Length <= byteIndex!");
			Check.Cond(bytes.Length > byteIndex + charCount, "bytes.Length <= byteIndex+charCount");
			for(int i = 0; i < charCount; ++i) {
				bytes[i + byteIndex] = (byte)(chars[charIndex + i]);
			}
			return charCount;
		}

		public override int GetCharCount(byte[] bytes, int index, int count) {
			Check.NotNull(bytes, "bytes");
			Check.RangeCond(index >= 0, "index < 0!");
			Check.RangeCond(count >= 0, "count < 0!");
			Check.RangeCond(bytes.Length > index + count, "chars.Length <= index + count!");
			return bytes.Length;
		}

		public override int GetChars(byte[] bytes, int byteIndex, int byteCount, char[] chars, int charIndex) {
			Check.NotNull(bytes, "bytes");
			Check.NotNull(chars, "chars");
			Check.RangeCond(charIndex >= 0, "charIndex < 0!");
            Check.RangeCond(byteCount >= 0, "byteCount < 0!");
            Check.RangeCond(byteIndex >= 0, "byteIndex < 0!");
			Check.RangeCond(bytes.Length > byteIndex + byteCount, "bytes.Length <= byteIndex + byteCount!");
			Check.RangeCond(chars.Length > charIndex, "chars.Length <= charIndex!");
			Check.Cond(chars.Length > charIndex + byteCount, "bytes.Length <= byteIndex+charCount!");
			for(int i = 0; i < byteCount; ++i) {
				chars[charIndex + i] = (char)bytes[byteIndex + i];
			}
			return byteCount;
		}

		public override int GetMaxByteCount(int charCount) {
			return charCount;
		}

		public override int GetMaxCharCount(int byteCount) {
			return byteCount;
		}
		#endregion
	}
	
	public static class Check {
		public static void NotNull<T>(T obj, String name) {
			if(obj == null)
				throw new ArgumentNullException(name);
		}

		public static void RangeCond(bool cond, String desc) {
			if(!cond)
				throw new ArgumentOutOfRangeException(desc);
		}

		public static void Cond(bool cond, String desc) {
			if(!cond)
				throw new ArgumentException(desc);
		}
	}
	
	public class FileList<T>:IList<T> {
		private List<T> _l = new List<T>();
		private String _filename;

		public FileList(String filename) {
			BinaryFormatter bf = new BinaryFormatter();
			this._filename = filename;
			try {
				using(FileStream fs = File.Open(filename, FileMode.Open, FileAccess.Read, FileShare.None)) {
					_l = (List<T>)bf.Deserialize(fs);
				}
			} catch(Exception) {
				_l = new List<T>();
			}
		}

		private void Save() {
			using(FileStream fs = File.Open(_filename, FileMode.Create, FileAccess.Write, FileShare.None)) {
				new BinaryFormatter().Serialize(fs, _l);
			}
		}
	#region IList[T] implementation
		public int IndexOf(T item) {
			return _l.IndexOf(item);
		}

		public void Insert(int index, T item) {
			_l.Insert(index, item);
			Save();
		}

		public void RemoveAt(int index) {
			_l.RemoveAt(index);
			Save();
		}

		public T this[int index] { 
			get {
				return _l[index];			
			}
			set {
				_l[index] = value;
				Save();
			}
		}
		
		
		
	#endregion

	#region IEnumerable[T] implementation
		public System.Collections.Generic.IEnumerator<T> GetEnumerator() {
			return _l.GetEnumerator();
		}

		System.Collections.IEnumerator System.Collections.IEnumerable.GetEnumerator() {
			return _l.GetEnumerator();
		}
	#endregion

	#region ICollection[T] implementation
		public void Add(T item) {
			_l.Add(item);
			Save();
		}

		public void Clear() {
			_l.Clear();
			Save();
		}

		public bool Contains(T item) {
			return _l.Contains(item);
		}

		public void CopyTo(T[] array, int arrayIndex) {
			_l.CopyTo(array, arrayIndex);
		}

		public bool Remove(T item) {
			try {
				return _l.Remove(item);
			} finally {
				Save();
			}
		}

		public int Count { get { return _l.Count; } }

		public bool IsReadOnly { get { return false; } }
	#endregion
		
	}

    public class DefaultDict<TKey, TValue> : IDictionary<TKey, TValue> {
        Dictionary<TKey, TValue> dict;
        Func<TValue> gen;

        public DefaultDict() {
            gen = () => default(TValue);
            this.dict = new Dictionary<TKey, TValue>();
        }
        public DefaultDict(Func<TValue> gen) {
            this.gen = gen;
            this.dict = new Dictionary<TKey, TValue>();
        }
        public DefaultDict(IDictionary<TKey, TValue> toCopy) {
            gen = () => default(TValue);
            this.dict = new Dictionary<TKey, TValue>(toCopy);
        }
        public DefaultDict(Func<TValue> gen, IEqualityComparer<TKey> keyCompare) {
            this.gen = gen;
            this.dict = new Dictionary<TKey, TValue>(keyCompare);
        }
        public DefaultDict(Func<TValue> gen, IDictionary<TKey, TValue> toCopy) {
            this.gen = gen;
            this.dict = new Dictionary<TKey, TValue>(toCopy);
        }

        public DefaultDict<TKey, TValue> Add(TKey key, TValue value) {
            dict.Add(key, value);
            return this;
        }
        void IDictionary<TKey, TValue>.Add(TKey key, TValue value) {
            dict.Add(key, value);
        }
        public DefaultDict<TKey, TValue> AddRange(IEnumerable<KeyValuePair<TKey, TValue>> range) {
            range.ForEach(el => dict.Add(el.Key, el.Value));
            return this;
        }
        public DefaultDict<TKey, TValue> AddRange(IEnumerable<Tuple<TKey, TValue>> range) {
            range.ForEach(el => dict.Add(el.Item1, el.Item2));
            return this;
        }

        public bool ContainsKey(TKey key) {
            return dict.ContainsKey(key);
        }

        public ICollection<TKey> Keys {
            get { return dict.Keys; }
        }

        public bool Remove(TKey key) {
            return dict.Remove(key);
        }

        public bool TryGetValue(TKey key, out TValue value) {
            return TryGetValue(key, out value);
        }

        public ICollection<TValue> Values {
            get { return dict.Values; }
        }

        public TValue this[TKey key] {
            get {
                TValue ret;
                if (dict.TryGetValue(key, out ret))
                    return ret;
                else {
                    ret = gen();
                    dict.Add(key, ret);
                    return ret;
                }
            }
            set {
                dict[key] = value;
            }
        }

        public void Add(KeyValuePair<TKey, TValue> item) {
            dict.Add(item.Key, item.Value);
        }

        public void Clear() {
            dict.Clear();
        }

        public bool Contains(KeyValuePair<TKey, TValue> item) {
            return dict.Contains(item);
        }

        public void CopyTo(KeyValuePair<TKey, TValue>[] array, int arrayIndex) {
            ((IDictionary<TKey, TValue>)dict).CopyTo(array, arrayIndex);
        }

        public int Count {
            get { return dict.Count; }
        }

        public bool IsReadOnly {
            get { return false; }
        }

        public bool Remove(KeyValuePair<TKey, TValue> item) {
            return dict.Remove(item.Key);
        }

        public IEnumerator<KeyValuePair<TKey, TValue>> GetEnumerator() {
            return dict.GetEnumerator();
        }

        IEnumerator IEnumerable.GetEnumerator() {
            return dict.GetEnumerator();
        }
    }

	public static class ExtMethods {
		
		public static T2 Get<T1, T2>(this Dictionary<T1, T2> dict, T1 key, T2 defaultValue) {
			T2 ret;
			if(dict.TryGetValue(key, out ret))
				return ret;
			return defaultValue;
		}

		public static void ForEach<T>(this IEnumerable<T> list, Action<T> f) {
			foreach(T t in list)
				f(t);
		}

		public static string Join(this IEnumerable<string> list, string glue) {
			StringBuilder ret = list.Aggregate(new StringBuilder(), (sb, a) => sb.Append(a).Append(glue));
			if(ret.Length > glue.Length)
				ret.Length -= glue.Length;
			return ret.ToString();
		}

		public static string Join<T>(this IEnumerable<T> list, string glue, Func<T, string> formatter) {
			StringBuilder ret = list.Aggregate(new StringBuilder(), (sb, a) => sb.Append(formatter(a)).Append(glue));
			if(ret.Length > glue.Length)
				ret.Length -= glue.Length;
			return ret.ToString();
		}

        public static void RealParallel<T>(this IEnumerable<T> list, Action<T> a) {
            List<System.Threading.Thread> threads = new List<System.Threading.Thread>();
            foreach (T el in list) {
                System.Threading.Thread t = new System.Threading.Thread(new System.Threading.ParameterizedThreadStart(obj => a((T)obj)));
                threads.Add(t);
                t.Start();
            }
            foreach (System.Threading.Thread t in threads)
                t.Join();
        }

		public static IEnumerable<string> Trim(this IEnumerable<string> l) {
			foreach(string s in l)
				yield return s.Trim();
		}
        public static T MaxElem<T>(this IEnumerable<T> l, Func<T, int> f) {
            bool first = true;
            int max = default(int);
            T ret = default(T);
            foreach (T el in l) {
                int possible_max = f(el);
                if (first || possible_max > max) {
                    max = possible_max;
                    ret = el;
                    first = false;
                }
            }
            if (first)
                throw new InvalidOperationException("Enumeration is empty!");
            return ret;
        }
        public static T MaxElem<T>(this IEnumerable<T> l, Func<T, long> f)  {
            bool first = true;
            long max = default(long);
            T ret = default(T);
            foreach (T el in l) {
                long possible_max = f(el);
                if (first || possible_max > max) {
                    max = possible_max;
                    ret = el;
                    first = false;
                }
            }
            if (first)
                throw new InvalidOperationException("Enumeration is empty!");
            return ret;
        }
        public static T MaxElem<T>(this IEnumerable<T> l, Func<T, double> f) {
            bool first = true;
            double max = default(double);
            T ret = default(T);
            foreach (T el in l) {
                double possible_max = f(el);
                if (first || possible_max > max) {
                    max = possible_max;
                    ret = el;
                    first = false;
                }
            }
            if (first)
                throw new InvalidOperationException("Enumeration is empty!");
            return ret;
        }
	}
    public static class Utils {
        public static void TimeLimited(int milliseconds, Action<object> a) {
            System.Threading.Thread t = new System.Threading.Thread(new System.Threading.ParameterizedThreadStart(a));
            t.Start();
            if (!t.Join(milliseconds))
                t.Abort();
        }
    }
    public static class Escape {
        public static String Xml(String unescaped) {
            return unescaped.Replace("&", "&amp;").Replace(">", "&gt;").Replace("<", "&lt;").Replace("'", "&apos;").Replace("\"", "&quot;");
        }
        public static String Html(String unescaped) {
            return Xml(unescaped);
        }
    }

	public class Re {
		public static Match Match(String input, String pattern) {
			return Regex.Match(input, pattern);
		}

		public static Match Match(String input, String pattern, RegexOptions options) {
            return Regex.Match(input, pattern, options);
		}

		public static bool IsMatch(String input, String pattern) {
			return Regex.IsMatch(input, pattern);
		}

		public static bool IsMatch(String pattern, String input, RegexOptions options) {
			return Regex.IsMatch(pattern, input, options);
		}

		public static IEnumerable<Match> Matches(String input, String pattern) {
			return Regex.Matches(input, pattern).OfType<Match>();
		}

        public static IEnumerable<Match> Matches(String input, String pattern, RegexOptions options) {
            return Regex.Matches(input, pattern, options).OfType<Match>();
		}

        public string Replace(String input, String pattern, String replacement) {
			return Regex.Replace(input, pattern, replacement);
		}

        public static string Replace(String input, String pattern, MatchEvaluator evaluator) {
            return Regex.Replace(input, pattern, evaluator);
		}

        public static string Replace(String input, String pattern, String replacement, RegexOptions options) {
			return Regex.Replace(input, pattern, replacement, options);
		}

		public static string Replace(String input, String pattern, MatchEvaluator evaluator, RegexOptions options) {
            return Regex.Replace(input, pattern, evaluator, options);
		}

        public static string[] Split(String input, String pattern) {
            return Regex.Split(input, pattern);
		}

        public static string[] Split(String input, String pattern, RegexOptions options) {
            return Regex.Split(input, pattern, options);
		}
		
		private Regex re;
		
		public Re(String pattern) {
			re = new Regex(pattern);
		}

		public Re(String pattern, RegexOptions options) {
			re = new Regex(pattern, options);
		}

		public Match Match(String input) {
			return re.Match(input);
		}

		public Match Match(String input, int startat) {
			return re.Match(input, startat);
		}

		public Match Match(String input, int startat, int length) {
			return re.Match(input, startat, length);
		}

		public bool IsMatch(String input) {
			return re.IsMatch(input);
		}

		public bool IsMatch(String input, int startat) {
			return re.IsMatch(input, startat);
		}

		public IEnumerable<Match> Matches(String input) {
			return re.Matches(input).OfType<Match>();
		}

		public IEnumerable<Match> Matches(String input, int startat) {
			return re.Matches(input, startat).OfType<Match>();
		}

		public string Replace(String input, String replacement) {
			return re.Replace(input, replacement);
		}

		public string Replace(String input, MatchEvaluator evaluator) {
			return re.Replace(input, evaluator);
		}

		public string Replace(String input, String replacement, int count) {
			return re.Replace(input, replacement, count);
		}

		public string Replace(String input, MatchEvaluator evaluator, int count) {
			return re.Replace(input, evaluator, count);
		}

		public string Replace(String input, String replacement, int count, int startat) {
			return re.Replace(input, replacement, count, startat);
		}

		public string Replace(String input, MatchEvaluator evaluator, int count, int startat) {
			return re.Replace(input, evaluator, count, startat);
		}

		public string[] Split(String input) {
			return re.Split(input);
		}

		public string[] Split(String input, int count) {
			return re.Split(input, count);
		}

		public string[] Split(String input, int count, int startat) {
			return re.Split(input, count, startat);
		}
	}
	
	public static class WebRq {
		public static String Get(String url) {
			return new HTTP().Get(url);
		}

		public static byte[] BytesGet(String url) {
			return new HTTP().BytesGet(url);
		}
	
		public static String Post(String url, IEnumerable<Tuple<String, String>> post) {
			return new HTTP().Post(url, post);
		}

		public static String Post(String url, IEnumerable<KeyValuePair<string, string>> post) {
			return new HTTP().Post(url, post);
		}
		
		public static byte[] BytesPost(String url, IEnumerable<Tuple<String, String>> post) {
			return new HTTP().BytesPost(url, post);
		}
		
		public static String Head(String url) {
			return new HTTP().Head(url);
		}
	}

	public class HTTP {
		
		private Encoding _reqEnc = Encoding.UTF8;
		private Encoding _respEnc = null;
		private String _userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0";
		private CookieContainer _cookies = new CookieContainer();
		private HttpWebResponse _lastResponse = null;
		private int _lastStatus = 0;
		private Exception _lastError = null;
		private String _username = null;
		private String _password = null;
		private X509Certificate _cert = null;
		
		static HTTP() {
			ServicePointManager.ServerCertificateValidationCallback = (a,b,c,d) => true;
		}
		
	#region Öffentliche Methoden
		public String Get(String url) {
			return Do(url, "GET", null, null);
		}

		public byte[] BytesGet(String url) {
			return BytesDo(url, "GET", null, null);
		}

		public String Head(String url) {
			return Do(url, "HEAD", null, null);
		}
	
		public String Post(String url, IEnumerable<KeyValuePair<string, string>> post) {
			return Post(url, post.Select(kvp => new Tuple<string, string>(kvp.Key, kvp.Value)));
		}
		
		public String Post(String url, IEnumerable<Tuple<String, String>> post, String contentType=null) {
			byte[] data = _reqEnc.GetBytes(post.Join("&", kvp => Uri.EscapeDataString(kvp.Item1) + "=" + Uri.EscapeDataString(kvp.Item2)));
			return Do(url, "POST", data, contentType != null ? contentType : ("application/x-www-form-urlencoded ; charset=" + _reqEnc.BodyName));
		}
		
		public String Post(String url, byte[] data, String contentType) {
			Debug.Assert(contentType != null, "contentType darf nicht null sein!");
			return Do(url, "POST", data, contentType);
		}

		public byte[] BytesPost(String url, IEnumerable<Tuple<String, String>> post, String contentType=null) {
			byte[] data = _reqEnc.GetBytes(post.Join("&", kvp => Uri.EscapeDataString(kvp.Item1) + "=" + Uri.EscapeDataString(kvp.Item2)));
			return BytesDo(url, "POST", data, contentType != null ? contentType : ("application/x-www-form-urlencoded ; charset=" + _reqEnc.BodyName));
		}

		public String Put(String url, String contentType, byte[] data) {
			Debug.Assert(contentType != null, "contentType darf nicht null sein!");
			return Do(url, "PUT", data, contentType);
		}
	#endregion
	#region Eigenschaften
		public Encoding RequestEncoding { get { return _reqEnc; } set { _reqEnc = value; } }

		public Encoding ResponseEncoding { get { return _respEnc; } set { _respEnc = value; } }

		public String UserAgent { get { return _userAgent; } set { _userAgent = value; } }

		public String UserName { get { return _username; } set { _username = value; } }

		public String Password { get { return _password; } set { _password = value; } }
		
		public X509Certificate ClientCertificate { get { return _cert; } set { _cert = value; } }

		public int LastStatus { get { return _lastStatus; } }

		public Exception LastError { get { return _lastError; } }
	#endregion
		
		private byte[] BytesDo(String url, String method, byte[] data, String contentType) {
			Uri uri;
			if(!Uri.TryCreate(url, UriKind.Absolute, out uri) && !Uri.TryCreate("http://" + url, UriKind.Absolute, out uri)) {
				throw new InvalidOperationException("Kann mit der URI nichts anfangen!");
			}
			HttpWebRequest wr = (HttpWebRequest)HttpWebRequest.Create(uri);
			wr.CookieContainer = _cookies;
			wr.Method = method;
			if(_username != null && _password != null) {
				CredentialCache cc = new CredentialCache();
				cc.Add(uri, "Basic", new NetworkCredential(_username, _password));
				wr.PreAuthenticate = true;
				wr.Credentials = cc;
			}
			if(_cert != null) {
				wr.ClientCertificates.Add(_cert);
			}
			if(data != null) {
				wr.ContentType = contentType ?? "application/x-www-form-urlencoded";
				wr.ContentLength = data.Length;
				Stream reqS = wr.GetRequestStream();
				reqS.Write(data, 0, data.Length);
				reqS.Close();
			}
			wr.UserAgent = _userAgent;
			try {
				_lastResponse = (HttpWebResponse)wr.GetResponse();
			} catch(WebException e) { // wer baut sowas? -_-
				_lastError = e;
				_lastResponse = (HttpWebResponse)e.Response;
				_lastStatus = (int)_lastResponse.StatusCode;
			}
			try {
				Stream rs = _lastResponse.GetResponseStream();
				_lastStatus = (int)(_lastResponse.StatusCode);
				MemoryStream ms = new MemoryStream();
				rs.CopyTo(ms);
				return ms.ToArray();
			} finally {
				_lastResponse.Close();
			}
		}

		private String Do(String url, String method, byte[] data, String contentType) {
			byte[] resp = BytesDo(url, method, data, contentType);
			if(resp == null)
				return null;
			Encoding enc;
			try {
				if(_respEnc != null)
					enc = _respEnc;
				else
					enc = _lastResponse.ContentEncoding.Length > 0 ? Encoding.GetEncoding(_lastResponse.ContentEncoding) : Encoding.UTF8;
			} catch(ArgumentException) {
				enc = Encoding.UTF8;
			}
			return enc.GetString(resp);
		}
	}

    //http://www.codeproject.com/Articles/126751/Priority-queue-in-C-with-the-help-of-heap-data-str
    public class PriorityQueue<TPriority, TValue> {
        private List<KeyValuePair<TPriority, TValue>> _baseHeap;
        private IComparer<TPriority> _comparer;

        public PriorityQueue()
            : this(Comparer<TPriority>.Default) {
        }

        public PriorityQueue(IComparer<TPriority> comparer) {
            if (comparer == null)
                throw new ArgumentNullException();

            _baseHeap = new List<KeyValuePair<TPriority, TValue>>();
            _comparer = comparer;
        }

        public void Enqueue(TPriority priority, TValue value) {
            Insert(priority, value);
        }

        private void Insert(TPriority priority, TValue value) {
            KeyValuePair<TPriority, TValue> val =
                new KeyValuePair<TPriority, TValue>(priority, value);
            _baseHeap.Add(val);

            // heapify after insert, from end to beginning
            HeapifyFromEndToBeginning(_baseHeap.Count - 1);
        }

        private int HeapifyFromEndToBeginning(int pos) {
            if (pos >= _baseHeap.Count) return -1;

            // heap[i] have children heap[2*i + 1] and heap[2*i + 2] and parent heap[(i-1)/ 2];

            while (pos > 0) {
                int parentPos = (pos - 1) / 2;
                if (_comparer.Compare(_baseHeap[parentPos].Key, _baseHeap[pos].Key) > 0) {
                    ExchangeElements(parentPos, pos);
                    pos = parentPos;
                } else break;
            }
            return pos;
        }

        private void ExchangeElements(int pos1, int pos2) {
            KeyValuePair<TPriority, TValue> val = _baseHeap[pos1];
            _baseHeap[pos1] = _baseHeap[pos2];
            _baseHeap[pos2] = val;
        }
        public TValue DequeueValue() {
            return Dequeue().Value;
        }

        public KeyValuePair<TPriority, TValue> Dequeue() {
            if (!IsEmpty) {
                KeyValuePair<TPriority, TValue> result = _baseHeap[0];
                DeleteRoot();
                return result;
            } else
                throw new InvalidOperationException("Priority queue is empty");
        }

        private void DeleteRoot() {
            if (_baseHeap.Count <= 1) {
                _baseHeap.Clear();
                return;
            }

            _baseHeap[0] = _baseHeap[_baseHeap.Count - 1];
            _baseHeap.RemoveAt(_baseHeap.Count - 1);

            // heapify
            HeapifyFromBeginningToEnd(0);
        }

        private void HeapifyFromBeginningToEnd(int pos) {
            if (pos >= _baseHeap.Count) return;

            // heap[i] have children heap[2*i + 1] and heap[2*i + 2] and parent heap[(i-1)/ 2];

            while (true) {
                // on each iteration exchange element with its smallest child
                int smallest = pos;
                int left = 2 * pos + 1;
                int right = 2 * pos + 2;
                if (left < _baseHeap.Count &&
                    _comparer.Compare(_baseHeap[smallest].Key, _baseHeap[left].Key) > 0)
                    smallest = left;
                if (right < _baseHeap.Count &&
                    _comparer.Compare(_baseHeap[smallest].Key, _baseHeap[right].Key) > 0)
                    smallest = right;

                if (smallest != pos) {
                    ExchangeElements(smallest, pos);
                    pos = smallest;
                } else break;
            }
        }
        public KeyValuePair<TPriority, TValue> Peek() {
            if (!IsEmpty)
                return _baseHeap[0];
            else
                throw new InvalidOperationException("Priority queue is empty");
        }

        public TValue PeekValue() {
            return Peek().Value;
        }

        public bool IsEmpty {
            get { return _baseHeap.Count == 0; }
        }
    }

}

namespace Flow {
    using Utils;

    public class MaximumFlowNetwork {

        public class Pair<T1, T2> {
            public T1 Item1;
            public T2 Item2;
        }

        public MaximumFlowNetwork(int cNodes, int s, int t) {
            Check.RangeCond(s < cNodes, "s >= cNodes!");
            Check.RangeCond(t < cNodes, "t >= cNodes!");
            this.cNodes = cNodes;
            this.s = s;
            this.t = t;
            this.excess = new int[cNodes];
            height = new int[cNodes];
            c = new int[cNodes,cNodes];
            f = new int[cNodes, cNodes];
        }

        public readonly int cNodes;
        public readonly int s, t;
        private readonly int[] excess;
        private readonly int[] height;
        public readonly int[,] c;
        public readonly int[,] f;
        private void InitPP() {
            for (int i = 0; i < cNodes; ++i) {
                height[i] = 0;
                if (i != s && i != t)
                    excess[i] = c[s, i];
                else
                    excess[i] = 0;
                f[s, i] = c[s, i];
                f[i, s] = -c[s, i];
            }
            height[s] = cNodes;
        }
        private void Push(int u, int v) {
            int tmp = Math.Min(excess[u], c[u, v] - f[u, v]);
            f[u, v] += tmp;
            f[v, u] = -f[u, v];
            if (u != s && u != t)
                excess[u] -= tmp;
            if (v != s && v != t)
                excess[v] += tmp;
        }
        private void Lift(int u) {
            int min = 2 * cNodes;
            for (int i = 0; i < cNodes; ++i)
                if ((c[u,i] - f[u,i]) > 0 && height[i] + 1 < min)
                    min = height[i] + 1;
            height[u] = min;
        }
        public int MaxFlow() {
            //Highest-Label Preflow-Push
            //This is a stub implementation.
            //Should use Priority Queue instead of linear search
            InitPP();
            while (true) {
                int cur = -1;
                for (int i = 0; i < cNodes; ++i)
                    if (excess[i] > 0 && (cur == -1 || height[i] > height[cur]))
                        cur = i;
                if (cur == -1)
                    break;
                for (int i = 0; i < cNodes; ++i) {
                    if ((c[cur,i] - f[cur,i]) > 0 && (height[cur] == height[i] + 1))
                        Push(cur, i);
                    if (excess[cur] == 0)
                        break;
                }
                if (excess[cur] > 0)
                    Lift(cur);
            }

            int ret = 0;
            for (int i = 0; i < cNodes; ++i) {
                ret += f[s, i];
            }
            return ret;
        }

        public static void Test() {
            Debug.Assert(Test1());
            Debug.Assert(Test2());
            Debug.Assert(Test3());
            Debug.Assert(Test4());
            Debug.Assert(Test5());
            Debug.Assert(Test6());
        }

        private static bool Test1() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(2, 0, 1);
            net.c[0, 1] = 10;
            long flow = net.MaxFlow();
            return flow == 10;
        }
        private static bool Test2() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(3, 0, 2);
            net.c[0, 1] = 8;
            net.c[1, 2] = 12;
            long flow = net.MaxFlow();
            return flow == 8;
        }
        private static bool Test3() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(4, 0, 3);
            net.c[0, 1] = 7;
            net.c[0, 2] = 8;
            net.c[1, 3] = 3;
            net.c[2, 3] = 10;
            long flow = net.MaxFlow();
            return flow == 11;
        }
        private static bool Test4() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(4, 0, 3);
            net.c[0, 1] = 7;
            net.c[0, 2] = 8;
            net.c[1, 3] = 3;
            net.c[1, 2] = int.MaxValue;
            net.c[2, 3] = 10;
            long flow = net.MaxFlow();
            return flow == 13;
        }
        private static bool Test6() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(4, 2, 3);
            net.c[0, 1] = int.MaxValue;
            net.c[1, 0] = int.MaxValue;
            net.c[0, 3] = 17;
            net.c[2, 1] = 17;
            long flow = net.MaxFlow();
            return flow == 17;
        }
        private static bool Test5() {
            MaximumFlowNetwork net = new MaximumFlowNetwork(4, 0, 3);
            net.c[0, 1] = 17;
            net.c[1, 2] = int.MaxValue;
            net.c[2, 1] = int.MaxValue;
            net.c[2, 3] = 17;
            long flow = net.MaxFlow();
            return flow == 17;
        }
    }
    public class MinimumFlowNetwork {
        public readonly int cNodes;
        public readonly int s, t;
        private readonly int[] e; //excess
        private readonly int[] d; //distance
        public readonly int[,] l; //lower bound
        public readonly int[,] c; //capacity = upper bound
        private readonly int[,] f; //current flow
        private readonly bool[] enQ;

        public MinimumFlowNetwork(int cNodes, int s, int t) {
            Check.RangeCond(s < cNodes, "s >= cNodes");
            Check.RangeCond(t < cNodes, "t >= cNodes");
            Check.Cond(s != t, "s == t");
            this.cNodes = cNodes;
            this.s = s;
            this.t = t;
            e = new int[cNodes];
            d = new int[cNodes];
            l = new int[cNodes,cNodes];
            c = new int[cNodes, cNodes];
            f = new int[cNodes, cNodes];
            enQ = new bool[cNodes];
        }
        private IEnumerable<int> Neighbors(int node) {
            for (int i = 0; i < cNodes; ++i)
                if ((c[node, i] - f[node, i]) != 0)
                    yield return i;
        }
        private IEnumerable<int> Incoming(int node) {
            for (int i = 0; i < cNodes; ++i)
                if ((c[i, node] - f[i, node]) > 0)
                    yield return i;
        }
        private IEnumerable<int> Admissible(int node) {
            for (int i = 0; i < cNodes; ++i)
                if ((c[i, node] - f[i, node]) > 0 && d[node] == d[i] + 1)
                    yield return i;
        }
        private bool InitPP() {
            for (int i = 0; i < d.Length; ++i)
                d[i] = 0;
            Queue<int> Q = new Queue<int>();
            d[s] = -1;
            Q.Enqueue(s);
            while (Q.Count > 0) {
                int node = Q.Dequeue();
                foreach (int neighb in Neighbors(node)) {
                    if (d[neighb] == -1) {
                        d[neighb] = d[node] + 1;
                        Q.Enqueue(neighb);
                    }
                }
            }
            return d[t] != -1;
        }

        private bool GenFeasibleF() {
            MaximumFlowNetwork maxfl = new MaximumFlowNetwork(cNodes + 2, cNodes, cNodes + 1);
            int sum_s = 0;
            for (int i = 0; i < cNodes; ++i) {
                int b = 0;
                for (int j = 0; j < cNodes; ++j) {
                    maxfl.c[i, j] = c[i, j] - l[i, j];
                    b += l[j, i] - l[i, j];
                }
                if (b > 0) {
                    maxfl.c[maxfl.s, i] = b;
                    sum_s += b;
                } else {
                    maxfl.c[i, maxfl.t] = -b;
                }
            }
            maxfl.c[t, s] = int.MaxValue;
            int flow = maxfl.MaxFlow();
            if (sum_s != flow)
                return false;
            for (int i = 0; i < cNodes; ++i) {
                for (int j = 0; j < cNodes; ++j)
                    f[i, j] = maxfl.f[i, j] + l[i, j];
            }
            f[t, s] -= sum_s;
            f[s, t] += sum_s;
            return true;
        }

        public int MinFlow() {
            if (!GenFeasibleF())
                return -1;
            MaximumFlowNetwork back = new MaximumFlowNetwork(cNodes, t, s);
            for (int i = 0; i < cNodes; ++i) {
                for (int j = 0; j < cNodes; ++j) {
                    back.c[i, j] = c[i, j] - f[i, j];
                }
            }
            int res = back.MaxFlow();
            int ret = 0;
            for (int i = 0; i < cNodes; ++i)
                ret += f[s, i] + back.f[s, i];
            return ret;
        }

        public void Save(String filename) {
            using (StreamWriter w = new StreamWriter(filename)) {
                w.WriteLine("n=" + cNodes);
                w.WriteLine("s=" + s);
                w.WriteLine("t=" + t);
                for (int i = 0; i < cNodes; ++i) {
                    w.Write(i + ": ");
                    for (int j = 0; j < cNodes; ++j) {
                        if(c[i,j] == 0 && l[i,j] == 0)
                            continue;
                        w.Write(j + "l" + l[i, j] + "c" + c[i, j] + " ");
                    }
                    w.WriteLine();
                }
            }
        }
        public static MinimumFlowNetwork Load(String filename) {
            int state = 0;
            int cNodes=0, s=0, t=0;
            MinimumFlowNetwork ret=null;
            using (StreamReader r = new StreamReader(filename)) {
                while (!r.EndOfStream) {
                    String line = r.ReadLine();
                    switch (state) {
                        case 0: {
                                Match m = Regex.Match(line, "n=(\\d+)");
                                if (!m.Success)
                                    continue;
                                cNodes = int.Parse(m.Groups[1].Value);
                                ++state;
                            }
                            break;
                        case 1: {
                                Match m = Regex.Match(line, "s=(\\d+)");
                                if (!m.Success)
                                    continue;
                                s = int.Parse(m.Groups[1].Value);
                                ++state;
                            }
                            break;
                        case 2: {
                                Match m = Regex.Match(line, "t=(\\d+)");
                                if (!m.Success)
                                    continue;
                                t = int.Parse(m.Groups[1].Value);
                                ret = new MinimumFlowNetwork(cNodes, s, t);
                                ++state;
                            }
                            break;
                        case 3: {
                                Match m = Regex.Match(line, @"(\d+):((?: \d+l\d+c\d+)*)");
                                if (!m.Success)
                                    continue;
                                int i = int.Parse(m.Groups[1].Value);
                                foreach (Match inner in Regex.Matches(m.Groups[2].Value, @" (\d+)l(\d+)c(\d+)")) {
                                    int j = int.Parse(inner.Groups[1].Value);
                                    int l = int.Parse(inner.Groups[2].Value);
                                    int c = int.Parse(inner.Groups[3].Value);
                                    ret.l[i, j] = l;
                                    ret.c[i, j] = c;
                                }
                            }
                            break;
                    }
                }
                if (state != 3)
                    throw new FileLoadException("Laden fehlgeschlagen!");
                return ret;
            }
        }

#region Tests
        public static void Test() {
            Debug.Assert(Test3());
            Debug.Assert(Test1());
            Debug.Assert(Test2());
            Debug.Assert(Test4());
            Debug.Assert(Test5());
            Debug.Assert(Test6());
            Debug.Assert(Test7());
        }
        public static bool Test4() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(5, 0, 1);
            net.c[net.s, 2] = int.MaxValue;
            net.c[net.s, 4] = int.MaxValue;
            net.c[2, 3] = int.MaxValue;
            net.c[4, 3] = int.MaxValue;
            net.c[3, net.t] = int.MaxValue;
            net.c[4, net.t] = int.MaxValue;
            net.l[net.s, 4] = 1;
            net.l[2, 3] = 2;
            long flow = net.MinFlow();
            return flow == 3;
        }
        private static bool Test1() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(2, 0, 1);
            net.c[net.s, net.t] = int.MaxValue;
            net.l[net.s, net.t] = 17;
            long flow = net.MinFlow();
            return flow == 17;
        }
        private static bool Test2() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(2, 0, 1);
            net.c[net.s, net.t] = int.MaxValue;
            net.l[net.s, net.t] = 0;
            long flow = net.MinFlow();
            return flow == 0;
        }
        private static bool Test3() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(3, 0, 2);
            net.c[net.s, 1] = int.MaxValue;
            net.l[net.s, 1] = 10;
            net.c[1, net.t] = int.MaxValue;
            net.l[1, net.t] = 0;
            long flow = net.MinFlow();
            return flow == 10;
        }
        private static bool Test5() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(5, 0, 1);
            net.c[net.s, 2] = int.MaxValue;
            net.c[net.s, 4] = int.MaxValue;
            net.c[2, 3] = int.MaxValue;
            net.c[4, 3] = int.MaxValue;
            net.c[3, net.t] = int.MaxValue;
            net.c[4, net.t] = int.MaxValue;
            net.l[net.s, 4] = 1;
            net.l[4, net.t] = 20;
            net.l[2, 3] = 2;
            long flow = net.MinFlow();
            return flow == 22;
        }
        private static bool Test6() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(5, 0, 1);
            net.c[net.s, 2] = int.MaxValue;
            net.c[net.s, 4] = int.MaxValue;
            net.c[2, 3] = int.MaxValue;
            net.c[3, 4] = int.MaxValue;
            net.c[3, net.t] = int.MaxValue;
            net.c[4, net.t] = int.MaxValue;
            net.l[net.s, 4] = 1;
            net.l[4, net.t] = 20;
            net.l[2, 3] = 2;
            long flow = net.MinFlow();
            return flow == 20;
        }
        private static bool Test7() {
            Flow.MinimumFlowNetwork net = new MinimumFlowNetwork(5, 0, 1);
            net.c[net.s, 2] = int.MaxValue;
            net.c[net.s, 4] = int.MaxValue;
            net.c[2, 3] = int.MaxValue;
            net.c[3, 4] = int.MaxValue;
            net.c[3, net.t] = int.MaxValue;
            net.c[4, net.t] = int.MaxValue;
            net.l[net.s, 4] = 1;
            net.l[4, net.t] = 20;
            net.l[2, 3] = 2;
            String file = Path.GetTempFileName();
            try {
                net.Save(file);
                MinimumFlowNetwork net2 = MinimumFlowNetwork.Load(file);
                bool ccheck = net2.c[net2.s, 2] == int.MaxValue && net2.c[net2.s, 4] == int.MaxValue && net2.c[2, 3] == int.MaxValue && net2.c[3, 4] == int.MaxValue && net2.c[3, net2.t] == int.MaxValue && net2.c[4, net2.t] == int.MaxValue;
                bool lcheck = net2.l[net2.s, 4] == 1 && net2.l[4, net2.t] == 20 && net2.l[2, 3] == 2;
                long flow = net2.MinFlow();
                return ccheck && lcheck && flow == 20;
            } finally {
                File.Delete(file);
            }
        }
#endregion
    }
}

namespace Short {
    static class Foo {
        public static IEnumerable<TSource> W<TSource>(this IEnumerable<TSource> ie, Func<TSource, bool> predicate) {
            return ie.Where(predicate);
        }

        public static TSource A<TSource>(this IEnumerable<TSource> source, Func<TSource, TSource, TSource> func) {
            return source.Aggregate(func);
        }

        public static TAccumulate A<TSource, TAccumulate>(this IEnumerable<TSource> source, TAccumulate seed, Func<TAccumulate, TSource, TAccumulate> func) {
            return source.Aggregate(seed, func);
        }

        public static TResult A<TSource, TAccumulate, TResult>(this IEnumerable<TSource> source, TAccumulate seed, Func<TAccumulate, TSource, TAccumulate> func, Func<TAccumulate, TResult> resultSelector) {
            return source.Aggregate(seed, func, resultSelector);
        }

        public static IEnumerable<TResult> S<TSource, TResult>(this IEnumerable<TSource> source, Func<TSource, TResult> selector) {
            return source.Select(selector);
        }

        public static IEnumerable<TResult> SM<TSource, TCollection, TResult>(this IEnumerable<TSource> source, Func<TSource, IEnumerable<TResult>> selector) {
            return source.SelectMany(selector);
        }

        public static IEnumerable<TResult> SM<TSource, TCollection, TResult>(this IEnumerable<TSource> source, Func<TSource, IEnumerable<TCollection>> collectionSelector, Func<TSource, TCollection, TResult> resultSelector) {
            return source.SelectMany(collectionSelector, resultSelector);
        }
    }
}