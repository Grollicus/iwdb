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
			Check.RangeCond(index < 0, "index < 0");
			Check.RangeCond(count < 0, "count < 0");
			Check.RangeCond(chars.Length <= index + count, "chars.Length <= index + count");
			return count;
		}

		public override int GetBytes(char[] chars, int charIndex, int charCount, byte[] bytes, int byteIndex) {
			Check.NotNull(chars, "chars");
			Check.NotNull(bytes, "bytes");
			Check.RangeCond(charIndex < 0, "charIndex < 0");
			Check.RangeCond(charCount < 0, "charCount < 0");
			Check.RangeCond(byteIndex < 0, "byteIndex < 0");
			Check.RangeCond(chars.Length <= charIndex + charCount, "chars.Length <= charIndex+charCount");
			Check.RangeCond(bytes.Length <= byteIndex, "bytes.Length <= byteIndex");
			Check.Cond(bytes.Length <= byteIndex + charCount, "bytes.Length <= byteIndex+charCount");
			for(int i = 0; i < charCount; ++i) {
				bytes[i + byteIndex] = (byte)(chars[charIndex + i]);
			}
			return charCount;
		}

		public override int GetCharCount(byte[] bytes, int index, int count) {
			Check.NotNull(bytes, "bytes");
			Check.RangeCond(index < 0, "index < 0");
			Check.RangeCond(count < 0, "count < 0");
			Check.RangeCond(bytes.Length <= index + count, "chars.Length <= index + count");
			return bytes.Length;
		}

		public override int GetChars(byte[] bytes, int byteIndex, int byteCount, char[] chars, int charIndex) {
			Check.NotNull(bytes, "bytes");
			Check.NotNull(chars, "chars");
			Check.RangeCond(charIndex < 0, "charIndex < 0");
			Check.RangeCond(byteCount < 0, "byteCount < 0");
			Check.RangeCond(byteIndex < 0, "byteIndex < 0");
			Check.RangeCond(bytes.Length <= byteIndex + byteCount, "bytes.Length <= byteIndex + byteCount");
			Check.RangeCond(chars.Length <= charIndex, "chars.Length <= charIndex");
			Check.Cond(chars.Length <= charIndex + byteCount, "bytes.Length <= byteIndex+charCount");
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
			if(cond)
				throw new ArgumentOutOfRangeException(desc);
		}

		public static void Cond(bool cond, String desc) {
			if(cond)
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