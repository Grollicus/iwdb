using System;
using System.Collections.Generic;
using System.Text;

namespace IWDB {
	public class OrderedList<ItemType>:ICollection<ItemType> {
		
		List<ItemType> list;
		IComparer<ItemType> comparer;

		public OrderedList() {
			this.comparer = null;
			list = new List<ItemType>();
		}
		public OrderedList(IComparer<ItemType> comparer) {
			this.comparer = comparer;
			list = new List<ItemType>();
		}

		#region ICollection<ItemType> Member

		public void Add(ItemType item) {
			int pos = list.BinarySearch(item, comparer);
			if (pos < 0)
				pos = ~pos;
			list.Insert(pos, item);
		}

		public void Clear() {
			list.Clear();
		}

		public bool Contains(ItemType item) {
			return (list.BinarySearch(item, comparer) >= 0);
		}

		public void CopyTo(ItemType[] array, int arrayIndex) {
			list.CopyTo(array, arrayIndex);
		}

		public int Count {
			get { return list.Count; }
		}

		public bool IsReadOnly {
			get { return false; }
		}

		public bool Remove(ItemType item) {
			int pos = list.BinarySearch(item, comparer);
			if (pos < 0) {
				return false;
			}
			list.RemoveAt(pos);
			return true;
		}

		#endregion
		#region IEnumerable<ItemType> Member

		public IEnumerator<ItemType> GetEnumerator() {
			return list.GetEnumerator();
		}

		#endregion
		#region IEnumerable Member

		System.Collections.IEnumerator System.Collections.IEnumerable.GetEnumerator() {
			return list.GetEnumerator();
		}

		#endregion
		public ItemType this[int i] {
			get { return list[i]; }
		}
		public List<OrderedListDifference<ItemType>> Differences(OrderedList<ItemType> toCompare) {
			List<OrderedListDifference<ItemType>> diffs = new List<OrderedListDifference<ItemType>>();
			int otherPos = 0;
			int pos = 0;
				while (pos < this.Count && otherPos < toCompare.Count) {
					ItemType item = this[pos];
					ItemType otherItem = toCompare[otherPos];
					int diff = comparer.Compare(item, otherItem);
					if (diff < 0) {
						diffs.Add(new OrderedListDifference<ItemType>(OrderedListDifferenceType.MissingInCompared, item));
						++pos;
					} else {
						if (diff == 0) {
							++pos;
							++otherPos;
						} else {
							diffs.Add(new OrderedListDifference<ItemType>(OrderedListDifferenceType.MissingInComparer, otherItem));
							++otherPos;
						}
					}
				}
			while (pos < this.Count) {
				diffs.Add(new OrderedListDifference<ItemType>(OrderedListDifferenceType.MissingInCompared, this[pos++]));
			}
			while (otherPos < toCompare.Count) {
				diffs.Add(new OrderedListDifference<ItemType>(OrderedListDifferenceType.MissingInComparer, toCompare[otherPos++]));
			}
			return diffs;
		}
		public List<ItemType> Similarities(OrderedList<ItemType> toCompare) {
			List<ItemType> sims = new List<ItemType>();
			int otherPos = 0;
			int pos = 0;
			while (pos < this.Count && otherPos < toCompare.Count) {
				ItemType item = this[pos];
				ItemType otherItem = toCompare[otherPos];
				int diff = comparer.Compare(item, otherItem);
				if (diff == 0) {
					sims.Add(item);
					++pos;
					++otherPos;
				} else {
					if (diff < 0) {
						++pos;
					} else {
						++otherPos;
					}
				}
			}
			return sims;
		}
		public List<List<ItemType>> LinearGroup(GroupingDelegate<ItemType> groupingDelegate) {
			List<List<ItemType>> groups = new List<List<ItemType>>();
			bool first = true;
			int oldGroupingValue = 0;
			List<ItemType> activeGroup = null;
			foreach (ItemType item in list) {
				int groupingValue = groupingDelegate(item);
				if (groupingValue != oldGroupingValue || first) {
					activeGroup = new List<ItemType>();
					groups.Add(activeGroup);
					oldGroupingValue = groupingValue;
					first = false;
				}
				activeGroup.Add(item);
			}
			return groups;
		}
		public int RemoveMatching(Predicate<ItemType> matchDelegate) {
			return list.RemoveAll(matchDelegate);
		}
	}
	public delegate int GroupingDelegate<ItemType>(ItemType item);
	public enum OrderedListDifferenceType {
		MissingInComparer,
		MissingInCompared,
	}
	public class OrderedListDifference<ItemType> {
		public readonly OrderedListDifferenceType Difference;
		public readonly ItemType Item;
		internal OrderedListDifference(OrderedListDifferenceType diffType, ItemType item) {
			this.Difference = diffType;
			this.Item = item;
		}
		public override string ToString() {
			return Difference.ToString() + " " + Item.ToString();
		}
	}
}
