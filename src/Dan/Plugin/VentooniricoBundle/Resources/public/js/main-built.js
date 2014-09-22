
// Underscore.js 1.4.4
// ===================

// > http://underscorejs.org
// > (c) 2009-2013 Jeremy Ashkenas, DocumentCloud Inc.
// > Underscore may be freely distributed under the MIT license.

// Baseline setup
// --------------
(function() {

  // Establish the root object, `window` in the browser, or `global` on the server.
  var root = this;

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Establish the object that gets returned to break out of a loop iteration.
  var breaker = {};

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

  // Create quick reference variables for speed access to core prototypes.
  var push             = ArrayProto.push,
      slice            = ArrayProto.slice,
      concat           = ArrayProto.concat,
      toString         = ObjProto.toString,
      hasOwnProperty   = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var
    nativeForEach      = ArrayProto.forEach,
    nativeMap          = ArrayProto.map,
    nativeReduce       = ArrayProto.reduce,
    nativeReduceRight  = ArrayProto.reduceRight,
    nativeFilter       = ArrayProto.filter,
    nativeEvery        = ArrayProto.every,
    nativeSome         = ArrayProto.some,
    nativeIndexOf      = ArrayProto.indexOf,
    nativeLastIndexOf  = ArrayProto.lastIndexOf,
    nativeIsArray      = Array.isArray,
    nativeKeys         = Object.keys,
    nativeBind         = FuncProto.bind;

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for the old `require()` API. If we're in
  // the browser, add `_` as a global object via a string identifier,
  // for Closure Compiler "advanced" mode.
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      exports = module.exports = _;
    }
    exports._ = _;
  } else {
    root._ = _;
  }

  // Current version.
  _.VERSION = '1.4.4';

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles objects with the built-in `forEach`, arrays, and raw objects.
  // Delegates to **ECMAScript 5**'s native `forEach` if available.
  var each = _.each = _.forEach = function(obj, iterator, context) {
    if (obj == null) return;
    if (nativeForEach && obj.forEach === nativeForEach) {
      obj.forEach(iterator, context);
    } else if (obj.length === +obj.length) {
      for (var i = 0, l = obj.length; i < l; i++) {
        if (iterator.call(context, obj[i], i, obj) === breaker) return;
      }
    } else {
      for (var key in obj) {
        if (_.has(obj, key)) {
          if (iterator.call(context, obj[key], key, obj) === breaker) return;
        }
      }
    }
  };

  // Return the results of applying the iterator to each element.
  // Delegates to **ECMAScript 5**'s native `map` if available.
  _.map = _.collect = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);
    each(obj, function(value, index, list) {
      results[results.length] = iterator.call(context, value, index, list);
    });
    return results;
  };

  var reduceError = 'Reduce of empty array with no initial value';

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`. Delegates to **ECMAScript 5**'s native `reduce` if available.
  _.reduce = _.foldl = _.inject = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduce && obj.reduce === nativeReduce) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduce(iterator, memo) : obj.reduce(iterator);
    }
    each(obj, function(value, index, list) {
      if (!initial) {
        memo = value;
        initial = true;
      } else {
        memo = iterator.call(context, memo, value, index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // The right-associative version of reduce, also known as `foldr`.
  // Delegates to **ECMAScript 5**'s native `reduceRight` if available.
  _.reduceRight = _.foldr = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduceRight && obj.reduceRight === nativeReduceRight) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduceRight(iterator, memo) : obj.reduceRight(iterator);
    }
    var length = obj.length;
    if (length !== +length) {
      var keys = _.keys(obj);
      length = keys.length;
    }
    each(obj, function(value, index, list) {
      index = keys ? keys[--length] : --length;
      if (!initial) {
        memo = obj[index];
        initial = true;
      } else {
        memo = iterator.call(context, memo, obj[index], index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, iterator, context) {
    var result;
    any(obj, function(value, index, list) {
      if (iterator.call(context, value, index, list)) {
        result = value;
        return true;
      }
    });
    return result;
  };

  // Return all the elements that pass a truth test.
  // Delegates to **ECMAScript 5**'s native `filter` if available.
  // Aliased as `select`.
  _.filter = _.select = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeFilter && obj.filter === nativeFilter) return obj.filter(iterator, context);
    each(obj, function(value, index, list) {
      if (iterator.call(context, value, index, list)) results[results.length] = value;
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, iterator, context) {
    return _.filter(obj, function(value, index, list) {
      return !iterator.call(context, value, index, list);
    }, context);
  };

  // Determine whether all of the elements match a truth test.
  // Delegates to **ECMAScript 5**'s native `every` if available.
  // Aliased as `all`.
  _.every = _.all = function(obj, iterator, context) {
    iterator || (iterator = _.identity);
    var result = true;
    if (obj == null) return result;
    if (nativeEvery && obj.every === nativeEvery) return obj.every(iterator, context);
    each(obj, function(value, index, list) {
      if (!(result = result && iterator.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if at least one element in the object matches a truth test.
  // Delegates to **ECMAScript 5**'s native `some` if available.
  // Aliased as `any`.
  var any = _.some = _.any = function(obj, iterator, context) {
    iterator || (iterator = _.identity);
    var result = false;
    if (obj == null) return result;
    if (nativeSome && obj.some === nativeSome) return obj.some(iterator, context);
    each(obj, function(value, index, list) {
      if (result || (result = iterator.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if the array or object contains a given value (using `===`).
  // Aliased as `include`.
  _.contains = _.include = function(obj, target) {
    if (obj == null) return false;
    if (nativeIndexOf && obj.indexOf === nativeIndexOf) return obj.indexOf(target) != -1;
    return any(obj, function(value) {
      return value === target;
    });
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = function(obj, method) {
    var args = slice.call(arguments, 2);
    var isFunc = _.isFunction(method);
    return _.map(obj, function(value) {
      return (isFunc ? method : value[method]).apply(value, args);
    });
  };

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, function(value){ return value[key]; });
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // containing specific `key:value` pairs.
  _.where = function(obj, attrs, first) {
    if (_.isEmpty(attrs)) return first ? null : [];
    return _[first ? 'find' : 'filter'](obj, function(value) {
      for (var key in attrs) {
        if (attrs[key] !== value[key]) return false;
      }
      return true;
    });
  };

  // Convenience version of a common use case of `find`: getting the first object
  // containing specific `key:value` pairs.
  _.findWhere = function(obj, attrs) {
    return _.where(obj, attrs, true);
  };

  // Return the maximum element or (element-based computation).
  // Can't optimize arrays of integers longer than 65,535 elements.
  // See: https://bugs.webkit.org/show_bug.cgi?id=80797
  _.max = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.max.apply(Math, obj);
    }
    if (!iterator && _.isEmpty(obj)) return -Infinity;
    var result = {computed : -Infinity, value: -Infinity};
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      computed >= result.computed && (result = {value : value, computed : computed});
    });
    return result.value;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.min.apply(Math, obj);
    }
    if (!iterator && _.isEmpty(obj)) return Infinity;
    var result = {computed : Infinity, value: Infinity};
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      computed < result.computed && (result = {value : value, computed : computed});
    });
    return result.value;
  };

  // Shuffle an array.
  _.shuffle = function(obj) {
    var rand;
    var index = 0;
    var shuffled = [];
    each(obj, function(value) {
      rand = _.random(index++);
      shuffled[index - 1] = shuffled[rand];
      shuffled[rand] = value;
    });
    return shuffled;
  };

  // An internal function to generate lookup iterators.
  var lookupIterator = function(value) {
    return _.isFunction(value) ? value : function(obj){ return obj[value]; };
  };

  // Sort the object's values by a criterion produced by an iterator.
  _.sortBy = function(obj, value, context) {
    var iterator = lookupIterator(value);
    return _.pluck(_.map(obj, function(value, index, list) {
      return {
        value : value,
        index : index,
        criteria : iterator.call(context, value, index, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index < right.index ? -1 : 1;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(obj, value, context, behavior) {
    var result = {};
    var iterator = lookupIterator(value || _.identity);
    each(obj, function(value, index) {
      var key = iterator.call(context, value, index, obj);
      behavior(result, key, value);
    });
    return result;
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = function(obj, value, context) {
    return group(obj, value, context, function(result, key, value) {
      (_.has(result, key) ? result[key] : (result[key] = [])).push(value);
    });
  };

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = function(obj, value, context) {
    return group(obj, value, context, function(result, key) {
      if (!_.has(result, key)) result[key] = 0;
      result[key]++;
    });
  };

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iterator, context) {
    iterator = iterator == null ? _.identity : lookupIterator(iterator);
    var value = iterator.call(context, obj);
    var low = 0, high = array.length;
    while (low < high) {
      var mid = (low + high) >>> 1;
      iterator.call(context, array[mid]) < value ? low = mid + 1 : high = mid;
    }
    return low;
  };

  // Safely convert anything iterable into a real, live array.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (obj.length === +obj.length) return _.map(obj, _.identity);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return (obj.length === +obj.length) ? obj.length : _.keys(obj).length;
  };

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null) return void 0;
    return (n != null) && !guard ? slice.call(array, 0, n) : array[0];
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N. The **guard** check allows it to work with
  // `_.map`.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, array.length - ((n == null) || guard ? 1 : n));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array. The **guard** check allows it to work with `_.map`.
  _.last = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n != null) && !guard) {
      return slice.call(array, Math.max(array.length - n, 0));
    } else {
      return array[array.length - 1];
    }
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array. The **guard**
  // check allows it to work with `_.map`.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, (n == null) || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, _.identity);
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, output) {
    each(input, function(value) {
      if (_.isArray(value)) {
        shallow ? push.apply(output, value) : flatten(value, shallow, output);
      } else {
        output.push(value);
      }
    });
    return output;
  };

  // Return a completely flattened version of an array.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, []);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = function(array) {
    return _.difference(array, slice.call(arguments, 1));
  };

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iterator, context) {
    if (_.isFunction(isSorted)) {
      context = iterator;
      iterator = isSorted;
      isSorted = false;
    }
    var initial = iterator ? _.map(array, iterator, context) : array;
    var results = [];
    var seen = [];
    each(initial, function(value, index) {
      if (isSorted ? (!index || seen[seen.length - 1] !== value) : !_.contains(seen, value)) {
        seen.push(value);
        results.push(array[index]);
      }
    });
    return results;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = function() {
    return _.uniq(concat.apply(ArrayProto, arguments));
  };

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var rest = slice.call(arguments, 1);
    return _.filter(_.uniq(array), function(item) {
      return _.every(rest, function(other) {
        return _.indexOf(other, item) >= 0;
      });
    });
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = function(array) {
    var rest = concat.apply(ArrayProto, slice.call(arguments, 1));
    return _.filter(array, function(value){ return !_.contains(rest, value); });
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = function() {
    var args = slice.call(arguments);
    var length = _.max(_.pluck(args, 'length'));
    var results = new Array(length);
    for (var i = 0; i < length; i++) {
      results[i] = _.pluck(args, "" + i);
    }
    return results;
  };

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values.
  _.object = function(list, values) {
    if (list == null) return {};
    var result = {};
    for (var i = 0, l = list.length; i < l; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // If the browser doesn't supply us with indexOf (I'm looking at you, **MSIE**),
  // we need this function. Return the position of the first occurrence of an
  // item in an array, or -1 if the item is not included in the array.
  // Delegates to **ECMAScript 5**'s native `indexOf` if available.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = function(array, item, isSorted) {
    if (array == null) return -1;
    var i = 0, l = array.length;
    if (isSorted) {
      if (typeof isSorted == 'number') {
        i = (isSorted < 0 ? Math.max(0, l + isSorted) : isSorted);
      } else {
        i = _.sortedIndex(array, item);
        return array[i] === item ? i : -1;
      }
    }
    if (nativeIndexOf && array.indexOf === nativeIndexOf) return array.indexOf(item, isSorted);
    for (; i < l; i++) if (array[i] === item) return i;
    return -1;
  };

  // Delegates to **ECMAScript 5**'s native `lastIndexOf` if available.
  _.lastIndexOf = function(array, item, from) {
    if (array == null) return -1;
    var hasIndex = from != null;
    if (nativeLastIndexOf && array.lastIndexOf === nativeLastIndexOf) {
      return hasIndex ? array.lastIndexOf(item, from) : array.lastIndexOf(item);
    }
    var i = (hasIndex ? from : array.length);
    while (i--) if (array[i] === item) return i;
    return -1;
  };

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (arguments.length <= 1) {
      stop = start || 0;
      start = 0;
    }
    step = arguments[2] || 1;

    var len = Math.max(Math.ceil((stop - start) / step), 0);
    var idx = 0;
    var range = new Array(len);

    while(idx < len) {
      range[idx++] = start;
      start += step;
    }

    return range;
  };

  // Function (ahem) Functions
  // ------------------

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
  // available.
  _.bind = function(func, context) {
    if (func.bind === nativeBind && nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
    var args = slice.call(arguments, 2);
    return function() {
      return func.apply(context, args.concat(slice.call(arguments)));
    };
  };

  // Partially apply a function by creating a version that has had some of its
  // arguments pre-filled, without changing its dynamic `this` context.
  _.partial = function(func) {
    var args = slice.call(arguments, 1);
    return function() {
      return func.apply(this, args.concat(slice.call(arguments)));
    };
  };

  // Bind all of an object's methods to that object. Useful for ensuring that
  // all callbacks defined on an object belong to it.
  _.bindAll = function(obj) {
    var funcs = slice.call(arguments, 1);
    if (funcs.length === 0) funcs = _.functions(obj);
    each(funcs, function(f) { obj[f] = _.bind(obj[f], obj); });
    return obj;
  };

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memo = {};
    hasher || (hasher = _.identity);
    return function() {
      var key = hasher.apply(this, arguments);
      return _.has(memo, key) ? memo[key] : (memo[key] = func.apply(this, arguments));
    };
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = function(func, wait) {
    var args = slice.call(arguments, 2);
    return setTimeout(function(){ return func.apply(null, args); }, wait);
  };

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = function(func) {
    return _.delay.apply(_, [func, 1].concat(slice.call(arguments, 1)));
  };

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time.
  _.throttle = function(func, wait) {
    var context, args, timeout, result;
    var previous = 0;
    var later = function() {
      previous = new Date;
      timeout = null;
      result = func.apply(context, args);
    };
    return function() {
      var now = new Date;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0) {
        clearTimeout(timeout);
        timeout = null;
        previous = now;
        result = func.apply(context, args);
      } else if (!timeout) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, result;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) result = func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) result = func.apply(context, args);
      return result;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = function(func) {
    var ran = false, memo;
    return function() {
      if (ran) return memo;
      ran = true;
      memo = func.apply(this, arguments);
      func = null;
      return memo;
    };
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return function() {
      var args = [func];
      push.apply(args, arguments);
      return wrapper.apply(this, args);
    };
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var funcs = arguments;
    return function() {
      var args = arguments;
      for (var i = funcs.length - 1; i >= 0; i--) {
        args = [funcs[i].apply(this, args)];
      }
      return args[0];
    };
  };

  // Returns a function that will only be executed after being called N times.
  _.after = function(times, func) {
    if (times <= 0) return func();
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Object Functions
  // ----------------

  // Retrieve the names of an object's properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`
  _.keys = nativeKeys || function(obj) {
    if (obj !== Object(obj)) throw new TypeError('Invalid object');
    var keys = [];
    for (var key in obj) if (_.has(obj, key)) keys[keys.length] = key;
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var values = [];
    for (var key in obj) if (_.has(obj, key)) values.push(obj[key]);
    return values;
  };

  // Convert an object into a list of `[key, value]` pairs.
  _.pairs = function(obj) {
    var pairs = [];
    for (var key in obj) if (_.has(obj, key)) pairs.push([key, obj[key]]);
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    for (var key in obj) if (_.has(obj, key)) result[obj[key]] = key;
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    each(keys, function(key) {
      if (key in obj) copy[key] = obj[key];
    });
    return copy;
  };

   // Return a copy of the object without the blacklisted properties.
  _.omit = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    for (var key in obj) {
      if (!_.contains(keys, key)) copy[key] = obj[key];
    }
    return copy;
  };

  // Fill in a given object with default properties.
  _.defaults = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          if (obj[prop] == null) obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Internal recursive comparison function for `isEqual`.
  var eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the Harmony `egal` proposal: http://wiki.ecmascript.org/doku.php?id=harmony:egal.
    if (a === b) return a !== 0 || 1 / a == 1 / b;
    // A strict comparison is necessary because `null == undefined`.
    if (a == null || b == null) return a === b;
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className != toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, dates, and booleans are compared by value.
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return a == String(b);
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive. An `egal` comparison is performed for
        // other numeric values.
        return a != +a ? b != +b : (a == 0 ? 1 / a == 1 / b : a == +b);
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a == +b;
      // RegExps are compared by their source patterns and flags.
      case '[object RegExp]':
        return a.source == b.source &&
               a.global == b.global &&
               a.multiline == b.multiline &&
               a.ignoreCase == b.ignoreCase;
    }
    if (typeof a != 'object' || typeof b != 'object') return false;
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] == a) return bStack[length] == b;
    }
    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);
    var size = 0, result = true;
    // Recursively compare objects and arrays.
    if (className == '[object Array]') {
      // Compare array lengths to determine if a deep comparison is necessary.
      size = a.length;
      result = size == b.length;
      if (result) {
        // Deep compare the contents, ignoring non-numeric properties.
        while (size--) {
          if (!(result = eq(a[size], b[size], aStack, bStack))) break;
        }
      }
    } else {
      // Objects with different constructors are not equivalent, but `Object`s
      // from different frames are.
      var aCtor = a.constructor, bCtor = b.constructor;
      if (aCtor !== bCtor && !(_.isFunction(aCtor) && (aCtor instanceof aCtor) &&
                               _.isFunction(bCtor) && (bCtor instanceof bCtor))) {
        return false;
      }
      // Deep compare objects.
      for (var key in a) {
        if (_.has(a, key)) {
          // Count the expected number of properties.
          size++;
          // Deep compare each member.
          if (!(result = _.has(b, key) && eq(a[key], b[key], aStack, bStack))) break;
        }
      }
      // Ensure that both objects contain the same number of properties.
      if (result) {
        for (key in b) {
          if (_.has(b, key) && !(size--)) break;
        }
        result = !size;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return result;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b, [], []);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (_.isArray(obj) || _.isString(obj)) return obj.length === 0;
    for (var key in obj) if (_.has(obj, key)) return false;
    return true;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) == '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    return obj === Object(obj);
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp.
  each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) == '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return !!(obj && _.has(obj, 'callee'));
    };
  }

  // Optimize `isFunction` if appropriate.
  if (typeof (/./) !== 'function') {
    _.isFunction = function(obj) {
      return typeof obj === 'function';
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return isFinite(obj) && !isNaN(parseFloat(obj));
  };

  // Is the given value `NaN`? (NaN is the only number which does not equal itself).
  _.isNaN = function(obj) {
    return _.isNumber(obj) && obj != +obj;
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) == '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, key) {
    return hasOwnProperty.call(obj, key);
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iterators.
  _.identity = function(value) {
    return value;
  };

  // Run a function **n** times.
  _.times = function(n, iterator, context) {
    var accum = Array(n);
    for (var i = 0; i < n; i++) accum[i] = iterator.call(context, i);
    return accum;
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };

  // List of HTML entities for escaping.
  var entityMap = {
    escape: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;',
      '/': '&#x2F;'
    }
  };
  entityMap.unescape = _.invert(entityMap.escape);

  // Regexes containing the keys and values listed immediately above.
  var entityRegexes = {
    escape:   new RegExp('[' + _.keys(entityMap.escape).join('') + ']', 'g'),
    unescape: new RegExp('(' + _.keys(entityMap.unescape).join('|') + ')', 'g')
  };

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  _.each(['escape', 'unescape'], function(method) {
    _[method] = function(string) {
      if (string == null) return '';
      return ('' + string).replace(entityRegexes[method], function(match) {
        return entityMap[method][match];
      });
    };
  });

  // If the value of the named property is a function then invoke it;
  // otherwise, return it.
  _.result = function(object, property) {
    if (object == null) return null;
    var value = object[property];
    return _.isFunction(value) ? value.call(object) : value;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    each(_.functions(obj), function(name){
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return result.call(this, func.apply(_, args));
      };
    });
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate    : /<%([\s\S]+?)%>/g,
    interpolate : /<%=([\s\S]+?)%>/g,
    escape      : /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'":      "'",
    '\\':     '\\',
    '\r':     'r',
    '\n':     'n',
    '\t':     't',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escaper = /\\|'|\r|\n|\t|\u2028|\u2029/g;

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  _.template = function(text, data, settings) {
    var render;
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = new RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset)
        .replace(escaper, function(match) { return '\\' + escapes[match]; });

      if (escape) {
        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
      }
      if (interpolate) {
        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
      }
      if (evaluate) {
        source += "';\n" + evaluate + "\n__p+='";
      }
      index = offset + match.length;
      return match;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + "return __p;\n";

    try {
      render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    if (data) return render(data, _);
    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled function source as a convenience for precompilation.
    template.source = 'function(' + (settings.variable || 'obj') + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function, which will delegate to the wrapper.
  _.chain = function(obj) {
    return _(obj).chain();
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var result = function(obj) {
    return this._chain ? _(obj).chain() : obj;
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name == 'shift' || name == 'splice') && obj.length === 0) delete obj[0];
      return result.call(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return result.call(this, method.apply(this._wrapped, arguments));
    };
  });

  _.extend(_.prototype, {

    // Start chaining a wrapped Underscore object.
    chain: function() {
      this._chain = true;
      return this;
    },

    // Extracts the result from a wrapped and chained object.
    value: function() {
      return this._wrapped;
    }

  });

}).call(this);

define("underscore", (function (global) {
    return function () {
        var ret, fn;
        return ret || global._;
    };
}(this)));

//     Backbone.js 1.0.0

//     (c) 2010-2013 Jeremy Ashkenas, DocumentCloud Inc.
//     Backbone may be freely distributed under the MIT license.
//     For all details and documentation:
//     http://backbonejs.org

(function(){

  // Initial Setup
  // -------------

  // Save a reference to the global object (`window` in the browser, `exports`
  // on the server).
  var root = this;

  // Save the previous value of the `Backbone` variable, so that it can be
  // restored later on, if `noConflict` is used.
  var previousBackbone = root.Backbone;

  // Create local references to array methods we'll want to use later.
  var array = [];
  var push = array.push;
  var slice = array.slice;
  var splice = array.splice;

  // The top-level namespace. All public Backbone classes and modules will
  // be attached to this. Exported for both the browser and the server.
  var Backbone;
  if (typeof exports !== 'undefined') {
    Backbone = exports;
  } else {
    Backbone = root.Backbone = {};
  }

  // Current version of the library. Keep in sync with `package.json`.
  Backbone.VERSION = '1.0.0';

  // Require Underscore, if we're on the server, and it's not already present.
  var _ = root._;
  if (!_ && (typeof require !== 'undefined')) _ = require('underscore');

  // For Backbone's purposes, jQuery, Zepto, Ender, or My Library (kidding) owns
  // the `$` variable.
  Backbone.$ = root.jQuery || root.Zepto || root.ender || root.$;

  // Runs Backbone.js in *noConflict* mode, returning the `Backbone` variable
  // to its previous owner. Returns a reference to this Backbone object.
  Backbone.noConflict = function() {
    root.Backbone = previousBackbone;
    return this;
  };

  // Turn on `emulateHTTP` to support legacy HTTP servers. Setting this option
  // will fake `"PUT"` and `"DELETE"` requests via the `_method` parameter and
  // set a `X-Http-Method-Override` header.
  Backbone.emulateHTTP = false;

  // Turn on `emulateJSON` to support legacy servers that can't deal with direct
  // `application/json` requests ... will encode the body as
  // `application/x-www-form-urlencoded` instead and will send the model in a
  // form param named `model`.
  Backbone.emulateJSON = false;

  // Backbone.Events
  // ---------------

  // A module that can be mixed in to *any object* in order to provide it with
  // custom events. You may bind with `on` or remove with `off` callback
  // functions to an event; `trigger`-ing an event fires all callbacks in
  // succession.
  //
  //     var object = {};
  //     _.extend(object, Backbone.Events);
  //     object.on('expand', function(){ alert('expanded'); });
  //     object.trigger('expand');
  //
  var Events = Backbone.Events = {

    // Bind an event to a `callback` function. Passing `"all"` will bind
    // the callback to all events fired.
    on: function(name, callback, context) {
      if (!eventsApi(this, 'on', name, [callback, context]) || !callback) return this;
      this._events || (this._events = {});
      var events = this._events[name] || (this._events[name] = []);
      events.push({callback: callback, context: context, ctx: context || this});
      return this;
    },

    // Bind an event to only be triggered a single time. After the first time
    // the callback is invoked, it will be removed.
    once: function(name, callback, context) {
      if (!eventsApi(this, 'once', name, [callback, context]) || !callback) return this;
      var self = this;
      var once = _.once(function() {
        self.off(name, once);
        callback.apply(this, arguments);
      });
      once._callback = callback;
      return this.on(name, once, context);
    },

    // Remove one or many callbacks. If `context` is null, removes all
    // callbacks with that function. If `callback` is null, removes all
    // callbacks for the event. If `name` is null, removes all bound
    // callbacks for all events.
    off: function(name, callback, context) {
      var retain, ev, events, names, i, l, j, k;
      if (!this._events || !eventsApi(this, 'off', name, [callback, context])) return this;
      if (!name && !callback && !context) {
        this._events = {};
        return this;
      }

      names = name ? [name] : _.keys(this._events);
      for (i = 0, l = names.length; i < l; i++) {
        name = names[i];
        if (events = this._events[name]) {
          this._events[name] = retain = [];
          if (callback || context) {
            for (j = 0, k = events.length; j < k; j++) {
              ev = events[j];
              if ((callback && callback !== ev.callback && callback !== ev.callback._callback) ||
                  (context && context !== ev.context)) {
                retain.push(ev);
              }
            }
          }
          if (!retain.length) delete this._events[name];
        }
      }

      return this;
    },

    // Trigger one or many events, firing all bound callbacks. Callbacks are
    // passed the same arguments as `trigger` is, apart from the event name
    // (unless you're listening on `"all"`, which will cause your callback to
    // receive the true name of the event as the first argument).
    trigger: function(name) {
      if (!this._events) return this;
      var args = slice.call(arguments, 1);
      if (!eventsApi(this, 'trigger', name, args)) return this;
      var events = this._events[name];
      var allEvents = this._events.all;
      if (events) triggerEvents(events, args);
      if (allEvents) triggerEvents(allEvents, arguments);
      return this;
    },

    // Tell this object to stop listening to either specific events ... or
    // to every object it's currently listening to.
    stopListening: function(obj, name, callback) {
      var listeners = this._listeners;
      if (!listeners) return this;
      var deleteListener = !name && !callback;
      if (typeof name === 'object') callback = this;
      if (obj) (listeners = {})[obj._listenerId] = obj;
      for (var id in listeners) {
        listeners[id].off(name, callback, this);
        if (deleteListener) delete this._listeners[id];
      }
      return this;
    }

  };

  // Regular expression used to split event strings.
  var eventSplitter = /\s+/;

  // Implement fancy features of the Events API such as multiple event
  // names `"change blur"` and jQuery-style event maps `{change: action}`
  // in terms of the existing API.
  var eventsApi = function(obj, action, name, rest) {
    if (!name) return true;

    // Handle event maps.
    if (typeof name === 'object') {
      for (var key in name) {
        obj[action].apply(obj, [key, name[key]].concat(rest));
      }
      return false;
    }

    // Handle space separated event names.
    if (eventSplitter.test(name)) {
      var names = name.split(eventSplitter);
      for (var i = 0, l = names.length; i < l; i++) {
        obj[action].apply(obj, [names[i]].concat(rest));
      }
      return false;
    }

    return true;
  };

  // A difficult-to-believe, but optimized internal dispatch function for
  // triggering events. Tries to keep the usual cases speedy (most internal
  // Backbone events have 3 arguments).
  var triggerEvents = function(events, args) {
    var ev, i = -1, l = events.length, a1 = args[0], a2 = args[1], a3 = args[2];
    switch (args.length) {
      case 0: while (++i < l) (ev = events[i]).callback.call(ev.ctx); return;
      case 1: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1); return;
      case 2: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1, a2); return;
      case 3: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1, a2, a3); return;
      default: while (++i < l) (ev = events[i]).callback.apply(ev.ctx, args);
    }
  };

  var listenMethods = {listenTo: 'on', listenToOnce: 'once'};

  // Inversion-of-control versions of `on` and `once`. Tell *this* object to
  // listen to an event in another object ... keeping track of what it's
  // listening to.
  _.each(listenMethods, function(implementation, method) {
    Events[method] = function(obj, name, callback) {
      var listeners = this._listeners || (this._listeners = {});
      var id = obj._listenerId || (obj._listenerId = _.uniqueId('l'));
      listeners[id] = obj;
      if (typeof name === 'object') callback = this;
      obj[implementation](name, callback, this);
      return this;
    };
  });

  // Aliases for backwards compatibility.
  Events.bind   = Events.on;
  Events.unbind = Events.off;

  // Allow the `Backbone` object to serve as a global event bus, for folks who
  // want global "pubsub" in a convenient place.
  _.extend(Backbone, Events);

  // Backbone.Model
  // --------------

  // Backbone **Models** are the basic data object in the framework --
  // frequently representing a row in a table in a database on your server.
  // A discrete chunk of data and a bunch of useful, related methods for
  // performing computations and transformations on that data.

  // Create a new model with the specified attributes. A client id (`cid`)
  // is automatically generated and assigned for you.
  var Model = Backbone.Model = function(attributes, options) {
    var defaults;
    var attrs = attributes || {};
    options || (options = {});
    this.cid = _.uniqueId('c');
    this.attributes = {};
    _.extend(this, _.pick(options, modelOptions));
    if (options.parse) attrs = this.parse(attrs, options) || {};
    if (defaults = _.result(this, 'defaults')) {
      attrs = _.defaults({}, attrs, defaults);
    }
    this.set(attrs, options);
    this.changed = {};
    this.initialize.apply(this, arguments);
  };

  // A list of options to be attached directly to the model, if provided.
  var modelOptions = ['url', 'urlRoot', 'collection'];

  // Attach all inheritable methods to the Model prototype.
  _.extend(Model.prototype, Events, {

    // A hash of attributes whose current and previous value differ.
    changed: null,

    // The value returned during the last failed validation.
    validationError: null,

    // The default name for the JSON `id` attribute is `"id"`. MongoDB and
    // CouchDB users may want to set this to `"_id"`.
    idAttribute: 'id',

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // Return a copy of the model's `attributes` object.
    toJSON: function(options) {
      return _.clone(this.attributes);
    },

    // Proxy `Backbone.sync` by default -- but override this if you need
    // custom syncing semantics for *this* particular model.
    sync: function() {
      return Backbone.sync.apply(this, arguments);
    },

    // Get the value of an attribute.
    get: function(attr) {
      return this.attributes[attr];
    },

    // Get the HTML-escaped value of an attribute.
    escape: function(attr) {
      return _.escape(this.get(attr));
    },

    // Returns `true` if the attribute contains a value that is not null
    // or undefined.
    has: function(attr) {
      return this.get(attr) != null;
    },

    // Set a hash of model attributes on the object, firing `"change"`. This is
    // the core primitive operation of a model, updating the data and notifying
    // anyone who needs to know about the change in state. The heart of the beast.
    set: function(key, val, options) {
      var attr, attrs, unset, changes, silent, changing, prev, current;
      if (key == null) return this;

      // Handle both `"key", value` and `{key: value}` -style arguments.
      if (typeof key === 'object') {
        attrs = key;
        options = val;
      } else {
        (attrs = {})[key] = val;
      }

      options || (options = {});

      // Run validation.
      if (!this._validate(attrs, options)) return false;

      // Extract attributes and options.
      unset           = options.unset;
      silent          = options.silent;
      changes         = [];
      changing        = this._changing;
      this._changing  = true;

      if (!changing) {
        this._previousAttributes = _.clone(this.attributes);
        this.changed = {};
      }
      current = this.attributes, prev = this._previousAttributes;

      // Check for changes of `id`.
      if (this.idAttribute in attrs) this.id = attrs[this.idAttribute];

      // For each `set` attribute, update or delete the current value.
      for (attr in attrs) {
        val = attrs[attr];
        if (!_.isEqual(current[attr], val)) changes.push(attr);
        if (!_.isEqual(prev[attr], val)) {
          this.changed[attr] = val;
        } else {
          delete this.changed[attr];
        }
        unset ? delete current[attr] : current[attr] = val;
      }

      // Trigger all relevant attribute changes.
      if (!silent) {
        if (changes.length) this._pending = true;
        for (var i = 0, l = changes.length; i < l; i++) {
          this.trigger('change:' + changes[i], this, current[changes[i]], options);
        }
      }

      // You might be wondering why there's a `while` loop here. Changes can
      // be recursively nested within `"change"` events.
      if (changing) return this;
      if (!silent) {
        while (this._pending) {
          this._pending = false;
          this.trigger('change', this, options);
        }
      }
      this._pending = false;
      this._changing = false;
      return this;
    },

    // Remove an attribute from the model, firing `"change"`. `unset` is a noop
    // if the attribute doesn't exist.
    unset: function(attr, options) {
      return this.set(attr, void 0, _.extend({}, options, {unset: true}));
    },

    // Clear all attributes on the model, firing `"change"`.
    clear: function(options) {
      var attrs = {};
      for (var key in this.attributes) attrs[key] = void 0;
      return this.set(attrs, _.extend({}, options, {unset: true}));
    },

    // Determine if the model has changed since the last `"change"` event.
    // If you specify an attribute name, determine if that attribute has changed.
    hasChanged: function(attr) {
      if (attr == null) return !_.isEmpty(this.changed);
      return _.has(this.changed, attr);
    },

    // Return an object containing all the attributes that have changed, or
    // false if there are no changed attributes. Useful for determining what
    // parts of a view need to be updated and/or what attributes need to be
    // persisted to the server. Unset attributes will be set to undefined.
    // You can also pass an attributes object to diff against the model,
    // determining if there *would be* a change.
    changedAttributes: function(diff) {
      if (!diff) return this.hasChanged() ? _.clone(this.changed) : false;
      var val, changed = false;
      var old = this._changing ? this._previousAttributes : this.attributes;
      for (var attr in diff) {
        if (_.isEqual(old[attr], (val = diff[attr]))) continue;
        (changed || (changed = {}))[attr] = val;
      }
      return changed;
    },

    // Get the previous value of an attribute, recorded at the time the last
    // `"change"` event was fired.
    previous: function(attr) {
      if (attr == null || !this._previousAttributes) return null;
      return this._previousAttributes[attr];
    },

    // Get all of the attributes of the model at the time of the previous
    // `"change"` event.
    previousAttributes: function() {
      return _.clone(this._previousAttributes);
    },

    // Fetch the model from the server. If the server's representation of the
    // model differs from its current attributes, they will be overridden,
    // triggering a `"change"` event.
    fetch: function(options) {
      options = options ? _.clone(options) : {};
      if (options.parse === void 0) options.parse = true;
      var model = this;
      var success = options.success;
      options.success = function(resp) {
        if (!model.set(model.parse(resp, options), options)) return false;
        if (success) success(model, resp, options);
        model.trigger('sync', model, resp, options);
      };
      wrapError(this, options);
      return this.sync('read', this, options);
    },

    // Set a hash of model attributes, and sync the model to the server.
    // If the server returns an attributes hash that differs, the model's
    // state will be `set` again.
    save: function(key, val, options) {
      var attrs, method, xhr, attributes = this.attributes;

      // Handle both `"key", value` and `{key: value}` -style arguments.
      if (key == null || typeof key === 'object') {
        attrs = key;
        options = val;
      } else {
        (attrs = {})[key] = val;
      }

      // If we're not waiting and attributes exist, save acts as `set(attr).save(null, opts)`.
      if (attrs && (!options || !options.wait) && !this.set(attrs, options)) return false;

      options = _.extend({validate: true}, options);

      // Do not persist invalid models.
      if (!this._validate(attrs, options)) return false;

      // Set temporary attributes if `{wait: true}`.
      if (attrs && options.wait) {
        this.attributes = _.extend({}, attributes, attrs);
      }

      // After a successful server-side save, the client is (optionally)
      // updated with the server-side state.
      if (options.parse === void 0) options.parse = true;
      var model = this;
      var success = options.success;
      options.success = function(resp) {
        // Ensure attributes are restored during synchronous saves.
        model.attributes = attributes;
        var serverAttrs = model.parse(resp, options);
        if (options.wait) serverAttrs = _.extend(attrs || {}, serverAttrs);
        if (_.isObject(serverAttrs) && !model.set(serverAttrs, options)) {
          return false;
        }
        if (success) success(model, resp, options);
        model.trigger('sync', model, resp, options);
      };
      wrapError(this, options);

      method = this.isNew() ? 'create' : (options.patch ? 'patch' : 'update');
      if (method === 'patch') options.attrs = attrs;
      xhr = this.sync(method, this, options);

      // Restore attributes.
      if (attrs && options.wait) this.attributes = attributes;

      return xhr;
    },

    // Destroy this model on the server if it was already persisted.
    // Optimistically removes the model from its collection, if it has one.
    // If `wait: true` is passed, waits for the server to respond before removal.
    destroy: function(options) {
      options = options ? _.clone(options) : {};
      var model = this;
      var success = options.success;

      var destroy = function() {
        model.trigger('destroy', model, model.collection, options);
      };

      options.success = function(resp) {
        if (options.wait || model.isNew()) destroy();
        if (success) success(model, resp, options);
        if (!model.isNew()) model.trigger('sync', model, resp, options);
      };

      if (this.isNew()) {
        options.success();
        return false;
      }
      wrapError(this, options);

      var xhr = this.sync('delete', this, options);
      if (!options.wait) destroy();
      return xhr;
    },

    // Default URL for the model's representation on the server -- if you're
    // using Backbone's restful methods, override this to change the endpoint
    // that will be called.
    url: function() {
      var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
      if (this.isNew()) return base;
      return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + encodeURIComponent(this.id);
    },

    // **parse** converts a response into the hash of attributes to be `set` on
    // the model. The default implementation is just to pass the response along.
    parse: function(resp, options) {
      return resp;
    },

    // Create a new model with identical attributes to this one.
    clone: function() {
      return new this.constructor(this.attributes);
    },

    // A model is new if it has never been saved to the server, and lacks an id.
    isNew: function() {
      return this.id == null;
    },

    // Check if the model is currently in a valid state.
    isValid: function(options) {
      return this._validate({}, _.extend(options || {}, { validate: true }));
    },

    // Run validation against the next complete set of model attributes,
    // returning `true` if all is well. Otherwise, fire an `"invalid"` event.
    _validate: function(attrs, options) {
      if (!options.validate || !this.validate) return true;
      attrs = _.extend({}, this.attributes, attrs);
      var error = this.validationError = this.validate(attrs, options) || null;
      if (!error) return true;
      this.trigger('invalid', this, error, _.extend(options || {}, {validationError: error}));
      return false;
    }

  });

  // Underscore methods that we want to implement on the Model.
  var modelMethods = ['keys', 'values', 'pairs', 'invert', 'pick', 'omit'];

  // Mix in each Underscore method as a proxy to `Model#attributes`.
  _.each(modelMethods, function(method) {
    Model.prototype[method] = function() {
      var args = slice.call(arguments);
      args.unshift(this.attributes);
      return _[method].apply(_, args);
    };
  });

  // Backbone.Collection
  // -------------------

  // If models tend to represent a single row of data, a Backbone Collection is
  // more analagous to a table full of data ... or a small slice or page of that
  // table, or a collection of rows that belong together for a particular reason
  // -- all of the messages in this particular folder, all of the documents
  // belonging to this particular author, and so on. Collections maintain
  // indexes of their models, both in order, and for lookup by `id`.

  // Create a new **Collection**, perhaps to contain a specific type of `model`.
  // If a `comparator` is specified, the Collection will maintain
  // its models in sort order, as they're added and removed.
  var Collection = Backbone.Collection = function(models, options) {
    options || (options = {});
    if (options.url) this.url = options.url;
    if (options.model) this.model = options.model;
    if (options.comparator !== void 0) this.comparator = options.comparator;
    this._reset();
    this.initialize.apply(this, arguments);
    if (models) this.reset(models, _.extend({silent: true}, options));
  };

  // Default options for `Collection#set`.
  var setOptions = {add: true, remove: true, merge: true};
  var addOptions = {add: true, merge: false, remove: false};

  // Define the Collection's inheritable methods.
  _.extend(Collection.prototype, Events, {

    // The default model for a collection is just a **Backbone.Model**.
    // This should be overridden in most cases.
    model: Model,

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // The JSON representation of a Collection is an array of the
    // models' attributes.
    toJSON: function(options) {
      return this.map(function(model){ return model.toJSON(options); });
    },

    // Proxy `Backbone.sync` by default.
    sync: function() {
      return Backbone.sync.apply(this, arguments);
    },

    // Add a model, or list of models to the set.
    add: function(models, options) {
      return this.set(models, _.defaults(options || {}, addOptions));
    },

    // Remove a model, or a list of models from the set.
    remove: function(models, options) {
      models = _.isArray(models) ? models.slice() : [models];
      options || (options = {});
      var i, l, index, model;
      for (i = 0, l = models.length; i < l; i++) {
        model = this.get(models[i]);
        if (!model) continue;
        delete this._byId[model.id];
        delete this._byId[model.cid];
        index = this.indexOf(model);
        this.models.splice(index, 1);
        this.length--;
        if (!options.silent) {
          options.index = index;
          model.trigger('remove', model, this, options);
        }
        this._removeReference(model);
      }
      return this;
    },

    // Update a collection by `set`-ing a new list of models, adding new ones,
    // removing models that are no longer present, and merging models that
    // already exist in the collection, as necessary. Similar to **Model#set**,
    // the core operation for updating the data contained by the collection.
    set: function(models, options) {
      options = _.defaults(options || {}, setOptions);
      if (options.parse) models = this.parse(models, options);
      if (!_.isArray(models)) models = models ? [models] : [];
      var i, l, model, attrs, existing, sort;
      var at = options.at;
      var sortable = this.comparator && (at == null) && options.sort !== false;
      var sortAttr = _.isString(this.comparator) ? this.comparator : null;
      var toAdd = [], toRemove = [], modelMap = {};

      // Turn bare objects into model references, and prevent invalid models
      // from being added.
      for (i = 0, l = models.length; i < l; i++) {
        if (!(model = this._prepareModel(models[i], options))) continue;

        // If a duplicate is found, prevent it from being added and
        // optionally merge it into the existing model.
        if (existing = this.get(model)) {
          if (options.remove) modelMap[existing.cid] = true;
          if (options.merge) {
            existing.set(model.attributes, options);
            if (sortable && !sort && existing.hasChanged(sortAttr)) sort = true;
          }

        // This is a new model, push it to the `toAdd` list.
        } else if (options.add) {
          toAdd.push(model);

          // Listen to added models' events, and index models for lookup by
          // `id` and by `cid`.
          model.on('all', this._onModelEvent, this);
          this._byId[model.cid] = model;
          if (model.id != null) this._byId[model.id] = model;
        }
      }

      // Remove nonexistent models if appropriate.
      if (options.remove) {
        for (i = 0, l = this.length; i < l; ++i) {
          if (!modelMap[(model = this.models[i]).cid]) toRemove.push(model);
        }
        if (toRemove.length) this.remove(toRemove, options);
      }

      // See if sorting is needed, update `length` and splice in new models.
      if (toAdd.length) {
        if (sortable) sort = true;
        this.length += toAdd.length;
        if (at != null) {
          splice.apply(this.models, [at, 0].concat(toAdd));
        } else {
          push.apply(this.models, toAdd);
        }
      }

      // Silently sort the collection if appropriate.
      if (sort) this.sort({silent: true});

      if (options.silent) return this;

      // Trigger `add` events.
      for (i = 0, l = toAdd.length; i < l; i++) {
        (model = toAdd[i]).trigger('add', model, this, options);
      }

      // Trigger `sort` if the collection was sorted.
      if (sort) this.trigger('sort', this, options);
      return this;
    },

    // When you have more items than you want to add or remove individually,
    // you can reset the entire set with a new list of models, without firing
    // any granular `add` or `remove` events. Fires `reset` when finished.
    // Useful for bulk operations and optimizations.
    reset: function(models, options) {
      options || (options = {});
      for (var i = 0, l = this.models.length; i < l; i++) {
        this._removeReference(this.models[i]);
      }
      options.previousModels = this.models;
      this._reset();
      this.add(models, _.extend({silent: true}, options));
      if (!options.silent) this.trigger('reset', this, options);
      return this;
    },

    // Add a model to the end of the collection.
    push: function(model, options) {
      model = this._prepareModel(model, options);
      this.add(model, _.extend({at: this.length}, options));
      return model;
    },

    // Remove a model from the end of the collection.
    pop: function(options) {
      var model = this.at(this.length - 1);
      this.remove(model, options);
      return model;
    },

    // Add a model to the beginning of the collection.
    unshift: function(model, options) {
      model = this._prepareModel(model, options);
      this.add(model, _.extend({at: 0}, options));
      return model;
    },

    // Remove a model from the beginning of the collection.
    shift: function(options) {
      var model = this.at(0);
      this.remove(model, options);
      return model;
    },

    // Slice out a sub-array of models from the collection.
    slice: function(begin, end) {
      return this.models.slice(begin, end);
    },

    // Get a model from the set by id.
    get: function(obj) {
      if (obj == null) return void 0;
      return this._byId[obj.id != null ? obj.id : obj.cid || obj];
    },

    // Get the model at the given index.
    at: function(index) {
      return this.models[index];
    },

    // Return models with matching attributes. Useful for simple cases of
    // `filter`.
    where: function(attrs, first) {
      if (_.isEmpty(attrs)) return first ? void 0 : [];
      return this[first ? 'find' : 'filter'](function(model) {
        for (var key in attrs) {
          if (attrs[key] !== model.get(key)) return false;
        }
        return true;
      });
    },

    // Return the first model with matching attributes. Useful for simple cases
    // of `find`.
    findWhere: function(attrs) {
      return this.where(attrs, true);
    },

    // Force the collection to re-sort itself. You don't need to call this under
    // normal circumstances, as the set will maintain sort order as each item
    // is added.
    sort: function(options) {
      if (!this.comparator) throw new Error('Cannot sort a set without a comparator');
      options || (options = {});

      // Run sort based on type of `comparator`.
      if (_.isString(this.comparator) || this.comparator.length === 1) {
        this.models = this.sortBy(this.comparator, this);
      } else {
        this.models.sort(_.bind(this.comparator, this));
      }

      if (!options.silent) this.trigger('sort', this, options);
      return this;
    },

    // Figure out the smallest index at which a model should be inserted so as
    // to maintain order.
    sortedIndex: function(model, value, context) {
      value || (value = this.comparator);
      var iterator = _.isFunction(value) ? value : function(model) {
        return model.get(value);
      };
      return _.sortedIndex(this.models, model, iterator, context);
    },

    // Pluck an attribute from each model in the collection.
    pluck: function(attr) {
      return _.invoke(this.models, 'get', attr);
    },

    // Fetch the default set of models for this collection, resetting the
    // collection when they arrive. If `reset: true` is passed, the response
    // data will be passed through the `reset` method instead of `set`.
    fetch: function(options) {
      options = options ? _.clone(options) : {};
      if (options.parse === void 0) options.parse = true;
      var success = options.success;
      var collection = this;
      options.success = function(resp) {
        var method = options.reset ? 'reset' : 'set';
        collection[method](resp, options);
        if (success) success(collection, resp, options);
        collection.trigger('sync', collection, resp, options);
      };
      wrapError(this, options);
      return this.sync('read', this, options);
    },

    // Create a new instance of a model in this collection. Add the model to the
    // collection immediately, unless `wait: true` is passed, in which case we
    // wait for the server to agree.
    create: function(model, options) {
      options = options ? _.clone(options) : {};
      if (!(model = this._prepareModel(model, options))) return false;
      if (!options.wait) this.add(model, options);
      var collection = this;
      var success = options.success;
      options.success = function(resp) {
        if (options.wait) collection.add(model, options);
        if (success) success(model, resp, options);
      };
      model.save(null, options);
      return model;
    },

    // **parse** converts a response into a list of models to be added to the
    // collection. The default implementation is just to pass it through.
    parse: function(resp, options) {
      return resp;
    },

    // Create a new collection with an identical list of models as this one.
    clone: function() {
      return new this.constructor(this.models);
    },

    // Private method to reset all internal state. Called when the collection
    // is first initialized or reset.
    _reset: function() {
      this.length = 0;
      this.models = [];
      this._byId  = {};
    },

    // Prepare a hash of attributes (or other model) to be added to this
    // collection.
    _prepareModel: function(attrs, options) {
      if (attrs instanceof Model) {
        if (!attrs.collection) attrs.collection = this;
        return attrs;
      }
      options || (options = {});
      options.collection = this;
      var model = new this.model(attrs, options);
      if (!model._validate(attrs, options)) {
        this.trigger('invalid', this, attrs, options);
        return false;
      }
      return model;
    },

    // Internal method to sever a model's ties to a collection.
    _removeReference: function(model) {
      if (this === model.collection) delete model.collection;
      model.off('all', this._onModelEvent, this);
    },

    // Internal method called every time a model in the set fires an event.
    // Sets need to update their indexes when models change ids. All other
    // events simply proxy through. "add" and "remove" events that originate
    // in other collections are ignored.
    _onModelEvent: function(event, model, collection, options) {
      if ((event === 'add' || event === 'remove') && collection !== this) return;
      if (event === 'destroy') this.remove(model, options);
      if (model && event === 'change:' + model.idAttribute) {
        delete this._byId[model.previous(model.idAttribute)];
        if (model.id != null) this._byId[model.id] = model;
      }
      this.trigger.apply(this, arguments);
    }

  });

  // Underscore methods that we want to implement on the Collection.
  // 90% of the core usefulness of Backbone Collections is actually implemented
  // right here:
  var methods = ['forEach', 'each', 'map', 'collect', 'reduce', 'foldl',
    'inject', 'reduceRight', 'foldr', 'find', 'detect', 'filter', 'select',
    'reject', 'every', 'all', 'some', 'any', 'include', 'contains', 'invoke',
    'max', 'min', 'toArray', 'size', 'first', 'head', 'take', 'initial', 'rest',
    'tail', 'drop', 'last', 'without', 'indexOf', 'shuffle', 'lastIndexOf',
    'isEmpty', 'chain'];

  // Mix in each Underscore method as a proxy to `Collection#models`.
  _.each(methods, function(method) {
    Collection.prototype[method] = function() {
      var args = slice.call(arguments);
      args.unshift(this.models);
      return _[method].apply(_, args);
    };
  });

  // Underscore methods that take a property name as an argument.
  var attributeMethods = ['groupBy', 'countBy', 'sortBy'];

  // Use attributes instead of properties.
  _.each(attributeMethods, function(method) {
    Collection.prototype[method] = function(value, context) {
      var iterator = _.isFunction(value) ? value : function(model) {
        return model.get(value);
      };
      return _[method](this.models, iterator, context);
    };
  });

  // Backbone.View
  // -------------

  // Backbone Views are almost more convention than they are actual code. A View
  // is simply a JavaScript object that represents a logical chunk of UI in the
  // DOM. This might be a single item, an entire list, a sidebar or panel, or
  // even the surrounding frame which wraps your whole app. Defining a chunk of
  // UI as a **View** allows you to define your DOM events declaratively, without
  // having to worry about render order ... and makes it easy for the view to
  // react to specific changes in the state of your models.

  // Creating a Backbone.View creates its initial element outside of the DOM,
  // if an existing element is not provided...
  var View = Backbone.View = function(options) {
    this.cid = _.uniqueId('view');
    this._configure(options || {});
    this._ensureElement();
    this.initialize.apply(this, arguments);
    this.delegateEvents();
  };

  // Cached regex to split keys for `delegate`.
  var delegateEventSplitter = /^(\S+)\s*(.*)$/;

  // List of view options to be merged as properties.
  var viewOptions = ['model', 'collection', 'el', 'id', 'attributes', 'className', 'tagName', 'events'];

  // Set up all inheritable **Backbone.View** properties and methods.
  _.extend(View.prototype, Events, {

    // The default `tagName` of a View's element is `"div"`.
    tagName: 'div',

    // jQuery delegate for element lookup, scoped to DOM elements within the
    // current view. This should be prefered to global lookups where possible.
    $: function(selector) {
      return this.$el.find(selector);
    },

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // **render** is the core function that your view should override, in order
    // to populate its element (`this.el`), with the appropriate HTML. The
    // convention is for **render** to always return `this`.
    render: function() {
      return this;
    },

    // Remove this view by taking the element out of the DOM, and removing any
    // applicable Backbone.Events listeners.
    remove: function() {
      this.$el.remove();
      this.stopListening();
      return this;
    },

    // Change the view's element (`this.el` property), including event
    // re-delegation.
    setElement: function(element, delegate) {
      if (this.$el) this.undelegateEvents();
      this.$el = element instanceof Backbone.$ ? element : Backbone.$(element);
      this.el = this.$el[0];
      if (delegate !== false) this.delegateEvents();
      return this;
    },

    // Set callbacks, where `this.events` is a hash of
    //
    // *{"event selector": "callback"}*
    //
    //     {
    //       'mousedown .title':  'edit',
    //       'click .button':     'save'
    //       'click .open':       function(e) { ... }
    //     }
    //
    // pairs. Callbacks will be bound to the view, with `this` set properly.
    // Uses event delegation for efficiency.
    // Omitting the selector binds the event to `this.el`.
    // This only works for delegate-able events: not `focus`, `blur`, and
    // not `change`, `submit`, and `reset` in Internet Explorer.
    delegateEvents: function(events) {
      if (!(events || (events = _.result(this, 'events')))) return this;
      this.undelegateEvents();
      for (var key in events) {
        var method = events[key];
        if (!_.isFunction(method)) method = this[events[key]];
        if (!method) continue;

        var match = key.match(delegateEventSplitter);
        var eventName = match[1], selector = match[2];
        method = _.bind(method, this);
        eventName += '.delegateEvents' + this.cid;
        if (selector === '') {
          this.$el.on(eventName, method);
        } else {
          this.$el.on(eventName, selector, method);
        }
      }
      return this;
    },

    // Clears all callbacks previously bound to the view with `delegateEvents`.
    // You usually don't need to use this, but may wish to if you have multiple
    // Backbone views attached to the same DOM element.
    undelegateEvents: function() {
      this.$el.off('.delegateEvents' + this.cid);
      return this;
    },

    // Performs the initial configuration of a View with a set of options.
    // Keys with special meaning *(e.g. model, collection, id, className)* are
    // attached directly to the view.  See `viewOptions` for an exhaustive
    // list.
    _configure: function(options) {
      if (this.options) options = _.extend({}, _.result(this, 'options'), options);
      _.extend(this, _.pick(options, viewOptions));
      this.options = options;
    },

    // Ensure that the View has a DOM element to render into.
    // If `this.el` is a string, pass it through `$()`, take the first
    // matching element, and re-assign it to `el`. Otherwise, create
    // an element from the `id`, `className` and `tagName` properties.
    _ensureElement: function() {
      if (!this.el) {
        var attrs = _.extend({}, _.result(this, 'attributes'));
        if (this.id) attrs.id = _.result(this, 'id');
        if (this.className) attrs['class'] = _.result(this, 'className');
        var $el = Backbone.$('<' + _.result(this, 'tagName') + '>').attr(attrs);
        this.setElement($el, false);
      } else {
        this.setElement(_.result(this, 'el'), false);
      }
    }

  });

  // Backbone.sync
  // -------------

  // Override this function to change the manner in which Backbone persists
  // models to the server. You will be passed the type of request, and the
  // model in question. By default, makes a RESTful Ajax request
  // to the model's `url()`. Some possible customizations could be:
  //
  // * Use `setTimeout` to batch rapid-fire updates into a single request.
  // * Send up the models as XML instead of JSON.
  // * Persist models via WebSockets instead of Ajax.
  //
  // Turn on `Backbone.emulateHTTP` in order to send `PUT` and `DELETE` requests
  // as `POST`, with a `_method` parameter containing the true HTTP method,
  // as well as all requests with the body as `application/x-www-form-urlencoded`
  // instead of `application/json` with the model in a param named `model`.
  // Useful when interfacing with server-side languages like **PHP** that make
  // it difficult to read the body of `PUT` requests.
  Backbone.sync = function(method, model, options) {
    var type = methodMap[method];

    // Default options, unless specified.
    _.defaults(options || (options = {}), {
      emulateHTTP: Backbone.emulateHTTP,
      emulateJSON: Backbone.emulateJSON
    });

    // Default JSON-request options.
    var params = {type: type, dataType: 'json'};

    // Ensure that we have a URL.
    if (!options.url) {
      params.url = _.result(model, 'url') || urlError();
    }

    // Ensure that we have the appropriate request data.
    if (options.data == null && model && (method === 'create' || method === 'update' || method === 'patch')) {
      params.contentType = 'application/json';
      params.data = JSON.stringify(options.attrs || model.toJSON(options));
    }

    // For older servers, emulate JSON by encoding the request into an HTML-form.
    if (options.emulateJSON) {
      params.contentType = 'application/x-www-form-urlencoded';
      params.data = params.data ? {model: params.data} : {};
    }

    // For older servers, emulate HTTP by mimicking the HTTP method with `_method`
    // And an `X-HTTP-Method-Override` header.
    if (options.emulateHTTP && (type === 'PUT' || type === 'DELETE' || type === 'PATCH')) {
      params.type = 'POST';
      if (options.emulateJSON) params.data._method = type;
      var beforeSend = options.beforeSend;
      options.beforeSend = function(xhr) {
        xhr.setRequestHeader('X-HTTP-Method-Override', type);
        if (beforeSend) return beforeSend.apply(this, arguments);
      };
    }

    // Don't process data on a non-GET request.
    if (params.type !== 'GET' && !options.emulateJSON) {
      params.processData = false;
    }

    // If we're sending a `PATCH` request, and we're in an old Internet Explorer
    // that still has ActiveX enabled by default, override jQuery to use that
    // for XHR instead. Remove this line when jQuery supports `PATCH` on IE8.
    if (params.type === 'PATCH' && window.ActiveXObject &&
          !(window.external && window.external.msActiveXFilteringEnabled)) {
      params.xhr = function() {
        return new ActiveXObject("Microsoft.XMLHTTP");
      };
    }

    // Make the request, allowing the user to override any Ajax options.
    var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
    model.trigger('request', model, xhr, options);
    return xhr;
  };

  // Map from CRUD to HTTP for our default `Backbone.sync` implementation.
  var methodMap = {
    'create': 'POST',
    'update': 'PUT',
    'patch':  'PATCH',
    'delete': 'DELETE',
    'read':   'GET'
  };

  // Set the default implementation of `Backbone.ajax` to proxy through to `$`.
  // Override this if you'd like to use a different library.
  Backbone.ajax = function() {
    return Backbone.$.ajax.apply(Backbone.$, arguments);
  };

  // Backbone.Router
  // ---------------

  // Routers map faux-URLs to actions, and fire events when routes are
  // matched. Creating a new one sets its `routes` hash, if not set statically.
  var Router = Backbone.Router = function(options) {
    options || (options = {});
    if (options.routes) this.routes = options.routes;
    this._bindRoutes();
    this.initialize.apply(this, arguments);
  };

  // Cached regular expressions for matching named param parts and splatted
  // parts of route strings.
  var optionalParam = /\((.*?)\)/g;
  var namedParam    = /(\(\?)?:\w+/g;
  var splatParam    = /\*\w+/g;
  var escapeRegExp  = /[\-{}\[\]+?.,\\\^$|#\s]/g;

  // Set up all inheritable **Backbone.Router** properties and methods.
  _.extend(Router.prototype, Events, {

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // Manually bind a single named route to a callback. For example:
    //
    //     this.route('search/:query/p:num', 'search', function(query, num) {
    //       ...
    //     });
    //
    route: function(route, name, callback) {
      if (!_.isRegExp(route)) route = this._routeToRegExp(route);
      if (_.isFunction(name)) {
        callback = name;
        name = '';
      }
      if (!callback) callback = this[name];
      var router = this;
      Backbone.history.route(route, function(fragment) {
        var args = router._extractParameters(route, fragment);
        callback && callback.apply(router, args);
        router.trigger.apply(router, ['route:' + name].concat(args));
        router.trigger('route', name, args);
        Backbone.history.trigger('route', router, name, args);
      });
      return this;
    },

    // Simple proxy to `Backbone.history` to save a fragment into the history.
    navigate: function(fragment, options) {
      Backbone.history.navigate(fragment, options);
      return this;
    },

    // Bind all defined routes to `Backbone.history`. We have to reverse the
    // order of the routes here to support behavior where the most general
    // routes can be defined at the bottom of the route map.
    _bindRoutes: function() {
      if (!this.routes) return;
      this.routes = _.result(this, 'routes');
      var route, routes = _.keys(this.routes);
      while ((route = routes.pop()) != null) {
        this.route(route, this.routes[route]);
      }
    },

    // Convert a route string into a regular expression, suitable for matching
    // against the current location hash.
    _routeToRegExp: function(route) {
      route = route.replace(escapeRegExp, '\\$&')
                   .replace(optionalParam, '(?:$1)?')
                   .replace(namedParam, function(match, optional){
                     return optional ? match : '([^\/]+)';
                   })
                   .replace(splatParam, '(.*?)');
      return new RegExp('^' + route + '$');
    },

    // Given a route, and a URL fragment that it matches, return the array of
    // extracted decoded parameters. Empty or unmatched parameters will be
    // treated as `null` to normalize cross-browser behavior.
    _extractParameters: function(route, fragment) {
      var params = route.exec(fragment).slice(1);
      return _.map(params, function(param) {
        return param ? decodeURIComponent(param) : null;
      });
    }

  });

  // Backbone.History
  // ----------------

  // Handles cross-browser history management, based on either
  // [pushState](http://diveintohtml5.info/history.html) and real URLs, or
  // [onhashchange](https://developer.mozilla.org/en-US/docs/DOM/window.onhashchange)
  // and URL fragments. If the browser supports neither (old IE, natch),
  // falls back to polling.
  var History = Backbone.History = function() {
    this.handlers = [];
    _.bindAll(this, 'checkUrl');

    // Ensure that `History` can be used outside of the browser.
    if (typeof window !== 'undefined') {
      this.location = window.location;
      this.history = window.history;
    }
  };

  // Cached regex for stripping a leading hash/slash and trailing space.
  var routeStripper = /^[#\/]|\s+$/g;

  // Cached regex for stripping leading and trailing slashes.
  var rootStripper = /^\/+|\/+$/g;

  // Cached regex for detecting MSIE.
  var isExplorer = /msie [\w.]+/;

  // Cached regex for removing a trailing slash.
  var trailingSlash = /\/$/;

  // Has the history handling already been started?
  History.started = false;

  // Set up all inheritable **Backbone.History** properties and methods.
  _.extend(History.prototype, Events, {

    // The default interval to poll for hash changes, if necessary, is
    // twenty times a second.
    interval: 50,

    // Gets the true hash value. Cannot use location.hash directly due to bug
    // in Firefox where location.hash will always be decoded.
    getHash: function(window) {
      var match = (window || this).location.href.match(/#(.*)$/);
      return match ? match[1] : '';
    },

    // Get the cross-browser normalized URL fragment, either from the URL,
    // the hash, or the override.
    getFragment: function(fragment, forcePushState) {
      if (fragment == null) {
        if (this._hasPushState || !this._wantsHashChange || forcePushState) {
          fragment = this.location.pathname;
          var root = this.root.replace(trailingSlash, '');
          if (!fragment.indexOf(root)) fragment = fragment.substr(root.length);
        } else {
          fragment = this.getHash();
        }
      }
      return fragment.replace(routeStripper, '');
    },

    // Start the hash change handling, returning `true` if the current URL matches
    // an existing route, and `false` otherwise.
    start: function(options) {
      if (History.started) throw new Error("Backbone.history has already been started");
      History.started = true;

      // Figure out the initial configuration. Do we need an iframe?
      // Is pushState desired ... is it available?
      this.options          = _.extend({}, {root: '/'}, this.options, options);
      this.root             = this.options.root;
      this._wantsHashChange = this.options.hashChange !== false;
      this._wantsPushState  = !!this.options.pushState;
      this._hasPushState    = !!(this.options.pushState && this.history && this.history.pushState);
      var fragment          = this.getFragment();
      var docMode           = document.documentMode;
      var oldIE             = (isExplorer.exec(navigator.userAgent.toLowerCase()) && (!docMode || docMode <= 7));

      // Normalize root to always include a leading and trailing slash.
      this.root = ('/' + this.root + '/').replace(rootStripper, '/');

      if (oldIE && this._wantsHashChange) {
        this.iframe = Backbone.$('<iframe src="javascript:0" tabindex="-1" />').hide().appendTo('body')[0].contentWindow;
        this.navigate(fragment);
      }

      // Depending on whether we're using pushState or hashes, and whether
      // 'onhashchange' is supported, determine how we check the URL state.
      if (this._hasPushState) {
        Backbone.$(window).on('popstate', this.checkUrl);
      } else if (this._wantsHashChange && ('onhashchange' in window) && !oldIE) {
        Backbone.$(window).on('hashchange', this.checkUrl);
      } else if (this._wantsHashChange) {
        this._checkUrlInterval = setInterval(this.checkUrl, this.interval);
      }

      // Determine if we need to change the base url, for a pushState link
      // opened by a non-pushState browser.
      this.fragment = fragment;
      var loc = this.location;
      var atRoot = loc.pathname.replace(/[^\/]$/, '$&/') === this.root;

      // If we've started off with a route from a `pushState`-enabled browser,
      // but we're currently in a browser that doesn't support it...
      if (this._wantsHashChange && this._wantsPushState && !this._hasPushState && !atRoot) {
        this.fragment = this.getFragment(null, true);
        this.location.replace(this.root + this.location.search + '#' + this.fragment);
        // Return immediately as browser will do redirect to new url
        return true;

      // Or if we've started out with a hash-based route, but we're currently
      // in a browser where it could be `pushState`-based instead...
      } else if (this._wantsPushState && this._hasPushState && atRoot && loc.hash) {
        this.fragment = this.getHash().replace(routeStripper, '');
        this.history.replaceState({}, document.title, this.root + this.fragment + loc.search);
      }

      if (!this.options.silent) return this.loadUrl();
    },

    // Disable Backbone.history, perhaps temporarily. Not useful in a real app,
    // but possibly useful for unit testing Routers.
    stop: function() {
      Backbone.$(window).off('popstate', this.checkUrl).off('hashchange', this.checkUrl);
      clearInterval(this._checkUrlInterval);
      History.started = false;
    },

    // Add a route to be tested when the fragment changes. Routes added later
    // may override previous routes.
    route: function(route, callback) {
      this.handlers.unshift({route: route, callback: callback});
    },

    // Checks the current URL to see if it has changed, and if it has,
    // calls `loadUrl`, normalizing across the hidden iframe.
    checkUrl: function(e) {
      var current = this.getFragment();
      if (current === this.fragment && this.iframe) {
        current = this.getFragment(this.getHash(this.iframe));
      }
      if (current === this.fragment) return false;
      if (this.iframe) this.navigate(current);
      this.loadUrl() || this.loadUrl(this.getHash());
    },

    // Attempt to load the current URL fragment. If a route succeeds with a
    // match, returns `true`. If no defined routes matches the fragment,
    // returns `false`.
    loadUrl: function(fragmentOverride) {
      var fragment = this.fragment = this.getFragment(fragmentOverride);
      var matched = _.any(this.handlers, function(handler) {
        if (handler.route.test(fragment)) {
          handler.callback(fragment);
          return true;
        }
      });
      return matched;
    },

    // Save a fragment into the hash history, or replace the URL state if the
    // 'replace' option is passed. You are responsible for properly URL-encoding
    // the fragment in advance.
    //
    // The options object can contain `trigger: true` if you wish to have the
    // route callback be fired (not usually desirable), or `replace: true`, if
    // you wish to modify the current URL without adding an entry to the history.
    navigate: function(fragment, options) {
      if (!History.started) return false;
      if (!options || options === true) options = {trigger: options};
      fragment = this.getFragment(fragment || '');
      if (this.fragment === fragment) return;
      this.fragment = fragment;
      var url = this.root + fragment;

      // If pushState is available, we use it to set the fragment as a real URL.
      if (this._hasPushState) {
        this.history[options.replace ? 'replaceState' : 'pushState']({}, document.title, url);

      // If hash changes haven't been explicitly disabled, update the hash
      // fragment to store history.
      } else if (this._wantsHashChange) {
        this._updateHash(this.location, fragment, options.replace);
        if (this.iframe && (fragment !== this.getFragment(this.getHash(this.iframe)))) {
          // Opening and closing the iframe tricks IE7 and earlier to push a
          // history entry on hash-tag change.  When replace is true, we don't
          // want this.
          if(!options.replace) this.iframe.document.open().close();
          this._updateHash(this.iframe.location, fragment, options.replace);
        }

      // If you've told us that you explicitly don't want fallback hashchange-
      // based history, then `navigate` becomes a page refresh.
      } else {
        return this.location.assign(url);
      }
      if (options.trigger) this.loadUrl(fragment);
    },

    // Update the hash location, either replacing the current entry, or adding
    // a new one to the browser history.
    _updateHash: function(location, fragment, replace) {
      if (replace) {
        var href = location.href.replace(/(javascript:|#).*$/, '');
        location.replace(href + '#' + fragment);
      } else {
        // Some browsers require that `hash` contains a leading #.
        location.hash = '#' + fragment;
      }
    }

  });

  // Create the default Backbone.history.
  Backbone.history = new History;

  // Helpers
  // -------

  // Helper function to correctly set up the prototype chain, for subclasses.
  // Similar to `goog.inherits`, but uses a hash of prototype properties and
  // class properties to be extended.
  var extend = function(protoProps, staticProps) {
    var parent = this;
    var child;

    // The constructor function for the new subclass is either defined by you
    // (the "constructor" property in your `extend` definition), or defaulted
    // by us to simply call the parent's constructor.
    if (protoProps && _.has(protoProps, 'constructor')) {
      child = protoProps.constructor;
    } else {
      child = function(){ return parent.apply(this, arguments); };
    }

    // Add static properties to the constructor function, if supplied.
    _.extend(child, parent, staticProps);

    // Set the prototype chain to inherit from `parent`, without calling
    // `parent`'s constructor function.
    var Surrogate = function(){ this.constructor = child; };
    Surrogate.prototype = parent.prototype;
    child.prototype = new Surrogate;

    // Add prototype properties (instance properties) to the subclass,
    // if supplied.
    if (protoProps) _.extend(child.prototype, protoProps);

    // Set a convenience property in case the parent's prototype is needed
    // later.
    child.__super__ = parent.prototype;

    return child;
  };

  // Set up inheritance for the model, collection, router, view and history.
  Model.extend = Collection.extend = Router.extend = View.extend = History.extend = extend;

  // Throw an error when a URL is needed, and none is supplied.
  var urlError = function() {
    throw new Error('A "url" property or function must be specified');
  };

  // Wrap an optional error callback with a fallback error event.
  var wrapError = function (model, options) {
    var error = options.error;
    options.error = function(resp) {
      if (error) error(model, resp, options);
      model.trigger('error', model, resp, options);
    };
  };

}).call(this);

define("backbone", ["underscore","jquery"], (function (global) {
    return function () {
        var ret, fn;
        return ret || global.Backbone;
    };
}(this)));

/*!
 * eventie v1.0.3
 * event binding helper
 *   eventie.bind( elem, 'click', myFn )
 *   eventie.unbind( elem, 'click', myFn )
 */

/*jshint browser: true, undef: true, unused: true */
/*global define: false */

( function( window ) {



var docElem = document.documentElement;

var bind = function() {};

if ( docElem.addEventListener ) {
  bind = function( obj, type, fn ) {
    obj.addEventListener( type, fn, false );
  };
} else if ( docElem.attachEvent ) {
  bind = function( obj, type, fn ) {
    obj[ type + fn ] = fn.handleEvent ?
      function() {
        var event = window.event;
        // add event.target
        event.target = event.target || event.srcElement;
        fn.handleEvent.call( fn, event );
      } :
      function() {
        var event = window.event;
        // add event.target
        event.target = event.target || event.srcElement;
        fn.call( obj, event );
      };
    obj.attachEvent( "on" + type, obj[ type + fn ] );
  };
}

var unbind = function() {};

if ( docElem.removeEventListener ) {
  unbind = function( obj, type, fn ) {
    obj.removeEventListener( type, fn, false );
  };
} else if ( docElem.detachEvent ) {
  unbind = function( obj, type, fn ) {
    obj.detachEvent( "on" + type, obj[ type + fn ] );
    try {
      delete obj[ type + fn ];
    } catch ( err ) {
      // can't delete window object properties
      obj[ type + fn ] = undefined;
    }
  };
}

var eventie = {
  bind: bind,
  unbind: unbind
};

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'eventie/eventie',eventie );
} else {
  // browser global
  window.eventie = eventie;
}

})( this );

/*!
 * docReady
 * Cross browser DOMContentLoaded event emitter
 */

/*jshint browser: true, strict: true, undef: true, unused: true*/
/*global define: false */

( function( window ) {



var document = window.document;
// collection of functions to be triggered on ready
var queue = [];

function docReady( fn ) {
  // throw out non-functions
  if ( typeof fn !== 'function' ) {
    return;
  }

  if ( docReady.isReady ) {
    // ready now, hit it
    fn();
  } else {
    // queue function when ready
    queue.push( fn );
  }
}

docReady.isReady = false;

// triggered on various doc ready events
function init( event ) {
  // bail if IE8 document is not ready just yet
  var isIE8NotReady = event.type === 'readystatechange' && document.readyState !== 'complete';
  if ( docReady.isReady || isIE8NotReady ) {
    return;
  }
  docReady.isReady = true;

  // process queue
  for ( var i=0, len = queue.length; i < len; i++ ) {
    var fn = queue[i];
    fn();
  }
}

function defineDocReady( eventie ) {
  eventie.bind( document, 'DOMContentLoaded', init );
  eventie.bind( document, 'readystatechange', init );
  eventie.bind( window, 'load', init );

  return docReady;
}

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  // if RequireJS, then doc is already ready
  docReady.isReady = typeof requirejs === 'function';
  define( 'doc-ready/doc-ready',[ 'eventie/eventie' ], defineDocReady );
} else {
  // browser global
  window.docReady = defineDocReady( window.eventie );
}

})( this );

/*!
 * EventEmitter v4.2.4 - git.io/ee
 * Oliver Caldwell
 * MIT license
 * @preserve
 */

(function () {
	

	/**
	 * Class for managing events.
	 * Can be extended to provide event functionality in other classes.
	 *
	 * @class EventEmitter Manages event registering and emitting.
	 */
	function EventEmitter() {}

	// Shortcuts to improve speed and size

	// Easy access to the prototype
	var proto = EventEmitter.prototype;

	/**
	 * Finds the index of the listener for the event in it's storage array.
	 *
	 * @param {Function[]} listeners Array of listeners to search through.
	 * @param {Function} listener Method to look for.
	 * @return {Number} Index of the specified listener, -1 if not found
	 * @api private
	 */
	function indexOfListener(listeners, listener) {
		var i = listeners.length;
		while (i--) {
			if (listeners[i].listener === listener) {
				return i;
			}
		}

		return -1;
	}

	/**
	 * Alias a method while keeping the context correct, to allow for overwriting of target method.
	 *
	 * @param {String} name The name of the target method.
	 * @return {Function} The aliased method
	 * @api private
	 */
	function alias(name) {
		return function aliasClosure() {
			return this[name].apply(this, arguments);
		};
	}

	/**
	 * Returns the listener array for the specified event.
	 * Will initialise the event object and listener arrays if required.
	 * Will return an object if you use a regex search. The object contains keys for each matched event. So /ba[rz]/ might return an object containing bar and baz. But only if you have either defined them with defineEvent or added some listeners to them.
	 * Each property in the object response is an array of listener functions.
	 *
	 * @param {String|RegExp} evt Name of the event to return the listeners from.
	 * @return {Function[]|Object} All listener functions for the event.
	 */
	proto.getListeners = function getListeners(evt) {
		var events = this._getEvents();
		var response;
		var key;

		// Return a concatenated array of all matching events if
		// the selector is a regular expression.
		if (typeof evt === 'object') {
			response = {};
			for (key in events) {
				if (events.hasOwnProperty(key) && evt.test(key)) {
					response[key] = events[key];
				}
			}
		}
		else {
			response = events[evt] || (events[evt] = []);
		}

		return response;
	};

	/**
	 * Takes a list of listener objects and flattens it into a list of listener functions.
	 *
	 * @param {Object[]} listeners Raw listener objects.
	 * @return {Function[]} Just the listener functions.
	 */
	proto.flattenListeners = function flattenListeners(listeners) {
		var flatListeners = [];
		var i;

		for (i = 0; i < listeners.length; i += 1) {
			flatListeners.push(listeners[i].listener);
		}

		return flatListeners;
	};

	/**
	 * Fetches the requested listeners via getListeners but will always return the results inside an object. This is mainly for internal use but others may find it useful.
	 *
	 * @param {String|RegExp} evt Name of the event to return the listeners from.
	 * @return {Object} All listener functions for an event in an object.
	 */
	proto.getListenersAsObject = function getListenersAsObject(evt) {
		var listeners = this.getListeners(evt);
		var response;

		if (listeners instanceof Array) {
			response = {};
			response[evt] = listeners;
		}

		return response || listeners;
	};

	/**
	 * Adds a listener function to the specified event.
	 * The listener will not be added if it is a duplicate.
	 * If the listener returns true then it will be removed after it is called.
	 * If you pass a regular expression as the event name then the listener will be added to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to attach the listener to.
	 * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addListener = function addListener(evt, listener) {
		var listeners = this.getListenersAsObject(evt);
		var listenerIsWrapped = typeof listener === 'object';
		var key;

		for (key in listeners) {
			if (listeners.hasOwnProperty(key) && indexOfListener(listeners[key], listener) === -1) {
				listeners[key].push(listenerIsWrapped ? listener : {
					listener: listener,
					once: false
				});
			}
		}

		return this;
	};

	/**
	 * Alias of addListener
	 */
	proto.on = alias('addListener');

	/**
	 * Semi-alias of addListener. It will add a listener that will be
	 * automatically removed after it's first execution.
	 *
	 * @param {String|RegExp} evt Name of the event to attach the listener to.
	 * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addOnceListener = function addOnceListener(evt, listener) {
		return this.addListener(evt, {
			listener: listener,
			once: true
		});
	};

	/**
	 * Alias of addOnceListener.
	 */
	proto.once = alias('addOnceListener');

	/**
	 * Defines an event name. This is required if you want to use a regex to add a listener to multiple events at once. If you don't do this then how do you expect it to know what event to add to? Should it just add to every possible match for a regex? No. That is scary and bad.
	 * You need to tell it what event names should be matched by a regex.
	 *
	 * @param {String} evt Name of the event to create.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.defineEvent = function defineEvent(evt) {
		this.getListeners(evt);
		return this;
	};

	/**
	 * Uses defineEvent to define multiple events.
	 *
	 * @param {String[]} evts An array of event names to define.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.defineEvents = function defineEvents(evts) {
		for (var i = 0; i < evts.length; i += 1) {
			this.defineEvent(evts[i]);
		}
		return this;
	};

	/**
	 * Removes a listener function from the specified event.
	 * When passed a regular expression as the event name, it will remove the listener from all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to remove the listener from.
	 * @param {Function} listener Method to remove from the event.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeListener = function removeListener(evt, listener) {
		var listeners = this.getListenersAsObject(evt);
		var index;
		var key;

		for (key in listeners) {
			if (listeners.hasOwnProperty(key)) {
				index = indexOfListener(listeners[key], listener);

				if (index !== -1) {
					listeners[key].splice(index, 1);
				}
			}
		}

		return this;
	};

	/**
	 * Alias of removeListener
	 */
	proto.off = alias('removeListener');

	/**
	 * Adds listeners in bulk using the manipulateListeners method.
	 * If you pass an object as the second argument you can add to multiple events at once. The object should contain key value pairs of events and listeners or listener arrays. You can also pass it an event name and an array of listeners to be added.
	 * You can also pass it a regular expression to add the array of listeners to all events that match it.
	 * Yeah, this function does quite a bit. That's probably a bad thing.
	 *
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add to multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to add.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addListeners = function addListeners(evt, listeners) {
		// Pass through to manipulateListeners
		return this.manipulateListeners(false, evt, listeners);
	};

	/**
	 * Removes listeners in bulk using the manipulateListeners method.
	 * If you pass an object as the second argument you can remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
	 * You can also pass it an event name and an array of listeners to be removed.
	 * You can also pass it a regular expression to remove the listeners from all events that match it.
	 *
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to remove from multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to remove.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeListeners = function removeListeners(evt, listeners) {
		// Pass through to manipulateListeners
		return this.manipulateListeners(true, evt, listeners);
	};

	/**
	 * Edits listeners in bulk. The addListeners and removeListeners methods both use this to do their job. You should really use those instead, this is a little lower level.
	 * The first argument will determine if the listeners are removed (true) or added (false).
	 * If you pass an object as the second argument you can add/remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
	 * You can also pass it an event name and an array of listeners to be added/removed.
	 * You can also pass it a regular expression to manipulate the listeners of all events that match it.
	 *
	 * @param {Boolean} remove True if you want to remove listeners, false if you want to add.
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add/remove from multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to add/remove.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.manipulateListeners = function manipulateListeners(remove, evt, listeners) {
		var i;
		var value;
		var single = remove ? this.removeListener : this.addListener;
		var multiple = remove ? this.removeListeners : this.addListeners;

		// If evt is an object then pass each of it's properties to this method
		if (typeof evt === 'object' && !(evt instanceof RegExp)) {
			for (i in evt) {
				if (evt.hasOwnProperty(i) && (value = evt[i])) {
					// Pass the single listener straight through to the singular method
					if (typeof value === 'function') {
						single.call(this, i, value);
					}
					else {
						// Otherwise pass back to the multiple function
						multiple.call(this, i, value);
					}
				}
			}
		}
		else {
			// So evt must be a string
			// And listeners must be an array of listeners
			// Loop over it and pass each one to the multiple method
			i = listeners.length;
			while (i--) {
				single.call(this, evt, listeners[i]);
			}
		}

		return this;
	};

	/**
	 * Removes all listeners from a specified event.
	 * If you do not specify an event then all listeners will be removed.
	 * That means every event will be emptied.
	 * You can also pass a regex to remove all events that match it.
	 *
	 * @param {String|RegExp} [evt] Optional name of the event to remove all listeners for. Will remove from every event if not passed.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeEvent = function removeEvent(evt) {
		var type = typeof evt;
		var events = this._getEvents();
		var key;

		// Remove different things depending on the state of evt
		if (type === 'string') {
			// Remove all listeners for the specified event
			delete events[evt];
		}
		else if (type === 'object') {
			// Remove all events matching the regex.
			for (key in events) {
				if (events.hasOwnProperty(key) && evt.test(key)) {
					delete events[key];
				}
			}
		}
		else {
			// Remove all listeners in all events
			delete this._events;
		}

		return this;
	};

	/**
	 * Alias of removeEvent.
	 *
	 * Added to mirror the node API.
	 */
	proto.removeAllListeners = alias('removeEvent');

	/**
	 * Emits an event of your choice.
	 * When emitted, every listener attached to that event will be executed.
	 * If you pass the optional argument array then those arguments will be passed to every listener upon execution.
	 * Because it uses `apply`, your array of arguments will be passed as if you wrote them out separately.
	 * So they will not arrive within the array on the other side, they will be separate.
	 * You can also pass a regular expression to emit to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
	 * @param {Array} [args] Optional array of arguments to be passed to each listener.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.emitEvent = function emitEvent(evt, args) {
		var listeners = this.getListenersAsObject(evt);
		var listener;
		var i;
		var key;
		var response;

		for (key in listeners) {
			if (listeners.hasOwnProperty(key)) {
				i = listeners[key].length;

				while (i--) {
					// If the listener returns true then it shall be removed from the event
					// The function is executed either with a basic call or an apply if there is an args array
					listener = listeners[key][i];

					if (listener.once === true) {
						this.removeListener(evt, listener.listener);
					}

					response = listener.listener.apply(this, args || []);

					if (response === this._getOnceReturnValue()) {
						this.removeListener(evt, listener.listener);
					}
				}
			}
		}

		return this;
	};

	/**
	 * Alias of emitEvent
	 */
	proto.trigger = alias('emitEvent');

	/**
	 * Subtly different from emitEvent in that it will pass its arguments on to the listeners, as opposed to taking a single array of arguments to pass on.
	 * As with emitEvent, you can pass a regex in place of the event name to emit to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
	 * @param {...*} Optional additional arguments to be passed to each listener.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.emit = function emit(evt) {
		var args = Array.prototype.slice.call(arguments, 1);
		return this.emitEvent(evt, args);
	};

	/**
	 * Sets the current value to check against when executing listeners. If a
	 * listeners return value matches the one set here then it will be removed
	 * after execution. This value defaults to true.
	 *
	 * @param {*} value The new value to check for when executing listeners.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.setOnceReturnValue = function setOnceReturnValue(value) {
		this._onceReturnValue = value;
		return this;
	};

	/**
	 * Fetches the current value to check against when executing listeners. If
	 * the listeners return value matches this one then it should be removed
	 * automatically. It will return true by default.
	 *
	 * @return {*|Boolean} The current value to check for or the default, true.
	 * @api private
	 */
	proto._getOnceReturnValue = function _getOnceReturnValue() {
		if (this.hasOwnProperty('_onceReturnValue')) {
			return this._onceReturnValue;
		}
		else {
			return true;
		}
	};

	/**
	 * Fetches the events object and creates one if required.
	 *
	 * @return {Object} The events storage object.
	 * @api private
	 */
	proto._getEvents = function _getEvents() {
		return this._events || (this._events = {});
	};

	// Expose the class either via AMD, CommonJS or the global object
	if (typeof define === 'function' && define.amd) {
		define('eventEmitter/EventEmitter',[],function () {
			return EventEmitter;
		});
	}
	else if (typeof module === 'object' && module.exports){
		module.exports = EventEmitter;
	}
	else {
		this.EventEmitter = EventEmitter;
	}
}.call(this));

/*!
 * getStyleProperty by kangax
 * http://perfectionkills.com/feature-testing-css-properties/
 */

/*jshint browser: true, strict: true, undef: true */
/*globals define: false */

( function( window ) {



var prefixes = 'Webkit Moz ms Ms O'.split(' ');
var docElemStyle = document.documentElement.style;

function getStyleProperty( propName ) {
  if ( !propName ) {
    return;
  }

  // test standard property first
  if ( typeof docElemStyle[ propName ] === 'string' ) {
    return propName;
  }

  // capitalize
  propName = propName.charAt(0).toUpperCase() + propName.slice(1);

  // test vendor specific properties
  var prefixed;
  for ( var i=0, len = prefixes.length; i < len; i++ ) {
    prefixed = prefixes[i] + propName;
    if ( typeof docElemStyle[ prefixed ] === 'string' ) {
      return prefixed;
    }
  }
}

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'get-style-property/get-style-property',[],function() {
    return getStyleProperty;
  });
} else {
  // browser global
  window.getStyleProperty = getStyleProperty;
}

})( window );

/**
 * getSize v1.1.5
 * measure size of elements
 */

/*jshint browser: true, strict: true, undef: true, unused: true */
/*global define: false */

( function( window, undefined ) {



// -------------------------- helpers -------------------------- //

var defView = document.defaultView;
var isComputedStyle = defView && defView.getComputedStyle;

var getStyle = isComputedStyle ?
  function( elem ) {
    return defView.getComputedStyle( elem, null );
  } :
  function( elem ) {
    return elem.currentStyle;
  };

// get a number from a string, not a percentage
function getStyleSize( value ) {
  var num = parseFloat( value );
  // not a percent like '100%', and a number
  var isValid = value.indexOf('%') === -1 && !isNaN( num );
  return isValid && num;
}

// -------------------------- measurements -------------------------- //

var measurements = [
  'paddingLeft',
  'paddingRight',
  'paddingTop',
  'paddingBottom',
  'marginLeft',
  'marginRight',
  'marginTop',
  'marginBottom',
  'borderLeftWidth',
  'borderRightWidth',
  'borderTopWidth',
  'borderBottomWidth'
];

function getZeroSize() {
  var size = {
    width: 0,
    height: 0,
    innerWidth: 0,
    innerHeight: 0,
    outerWidth: 0,
    outerHeight: 0
  };
  for ( var i=0, len = measurements.length; i < len; i++ ) {
    var measurement = measurements[i];
    size[ measurement ] = 0;
  }
  return size;
}



function defineGetSize( getStyleProperty ) {

// -------------------------- box sizing -------------------------- //

var boxSizingProp = getStyleProperty('boxSizing');
var isBoxSizeOuter;

/**
 * WebKit measures the outer-width on style.width on border-box elems
 * IE & Firefox measures the inner-width
 */
( function() {
  if ( !boxSizingProp ) {
    return;
  }

  var div = document.createElement('div');
  div.style.width = '200px';
  div.style.padding = '1px 2px 3px 4px';
  div.style.borderStyle = 'solid';
  div.style.borderWidth = '1px 2px 3px 4px';
  div.style[ boxSizingProp ] = 'border-box';

  var body = document.body || document.documentElement;
  body.appendChild( div );
  var style = getStyle( div );

  isBoxSizeOuter = getStyleSize( style.width ) === 200;
  body.removeChild( div );
})();


// -------------------------- getSize -------------------------- //

function getSize( elem ) {
  // use querySeletor if elem is string
  if ( typeof elem === 'string' ) {
    elem = document.querySelector( elem );
  }

  // do not proceed on non-objects
  if ( !elem || typeof elem !== 'object' || !elem.nodeType ) {
    return;
  }

  var style = getStyle( elem );

  // if hidden, everything is 0
  if ( style.display === 'none' ) {
    return getZeroSize();
  }

  var size = {};
  size.width = elem.offsetWidth;
  size.height = elem.offsetHeight;

  var isBorderBox = size.isBorderBox = !!( boxSizingProp &&
    style[ boxSizingProp ] && style[ boxSizingProp ] === 'border-box' );

  // get all measurements
  for ( var i=0, len = measurements.length; i < len; i++ ) {
    var measurement = measurements[i];
    var value = style[ measurement ];
    value = mungeNonPixel( elem, value );
    var num = parseFloat( value );
    // any 'auto', 'medium' value will be 0
    size[ measurement ] = !isNaN( num ) ? num : 0;
  }

  var paddingWidth = size.paddingLeft + size.paddingRight;
  var paddingHeight = size.paddingTop + size.paddingBottom;
  var marginWidth = size.marginLeft + size.marginRight;
  var marginHeight = size.marginTop + size.marginBottom;
  var borderWidth = size.borderLeftWidth + size.borderRightWidth;
  var borderHeight = size.borderTopWidth + size.borderBottomWidth;

  var isBorderBoxSizeOuter = isBorderBox && isBoxSizeOuter;

  // overwrite width and height if we can get it from style
  var styleWidth = getStyleSize( style.width );
  if ( styleWidth !== false ) {
    size.width = styleWidth +
      // add padding and border unless it's already including it
      ( isBorderBoxSizeOuter ? 0 : paddingWidth + borderWidth );
  }

  var styleHeight = getStyleSize( style.height );
  if ( styleHeight !== false ) {
    size.height = styleHeight +
      // add padding and border unless it's already including it
      ( isBorderBoxSizeOuter ? 0 : paddingHeight + borderHeight );
  }

  size.innerWidth = size.width - ( paddingWidth + borderWidth );
  size.innerHeight = size.height - ( paddingHeight + borderHeight );

  size.outerWidth = size.width + marginWidth;
  size.outerHeight = size.height + marginHeight;

  return size;
}

// IE8 returns percent values, not pixels
// taken from jQuery's curCSS
function mungeNonPixel( elem, value ) {
  // IE8 and has percent value
  if ( isComputedStyle || value.indexOf('%') === -1 ) {
    return value;
  }
  var style = elem.style;
	// Remember the original values
	var left = style.left;
	var rs = elem.runtimeStyle;
	var rsLeft = rs && rs.left;

	// Put in the new values to get a computed value out
	if ( rsLeft ) {
		rs.left = elem.currentStyle.left;
	}
	style.left = value;
	value = style.pixelLeft;

	// Revert the changed values
	style.left = left;
	if ( rsLeft ) {
		rs.left = rsLeft;
	}

  return value;
}

return getSize;

}

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'get-size/get-size',[ 'get-style-property/get-style-property' ], defineGetSize );
} else {
  // browser global
  window.getSize = defineGetSize( window.getStyleProperty );
}

})( window );

/**
 * matchesSelector helper v1.0.1
 *
 * @name matchesSelector
 *   @param {Element} elem
 *   @param {String} selector
 */

/*jshint browser: true, strict: true, undef: true, unused: true */
/*global define: false */

( function( global, ElemProto ) {

  

  var matchesMethod = ( function() {
    // check un-prefixed
    if ( ElemProto.matchesSelector ) {
      return 'matchesSelector';
    }
    // check vendor prefixes
    var prefixes = [ 'webkit', 'moz', 'ms', 'o' ];

    for ( var i=0, len = prefixes.length; i < len; i++ ) {
      var prefix = prefixes[i];
      var method = prefix + 'MatchesSelector';
      if ( ElemProto[ method ] ) {
        return method;
      }
    }
  })();

  // ----- match ----- //

  function match( elem, selector ) {
    return elem[ matchesMethod ]( selector );
  }

  // ----- appendToFragment ----- //

  function checkParent( elem ) {
    // not needed if already has parent
    if ( elem.parentNode ) {
      return;
    }
    var fragment = document.createDocumentFragment();
    fragment.appendChild( elem );
  }

  // ----- query ----- //

  // fall back to using QSA
  // thx @jonathantneal https://gist.github.com/3062955
  function query( elem, selector ) {
    // append to fragment if no parent
    checkParent( elem );

    // match elem with all selected elems of parent
    var elems = elem.parentNode.querySelectorAll( selector );
    for ( var i=0, len = elems.length; i < len; i++ ) {
      // return true if match
      if ( elems[i] === elem ) {
        return true;
      }
    }
    // otherwise return false
    return false;
  }

  // ----- matchChild ----- //

  function matchChild( elem, selector ) {
    checkParent( elem );
    return match( elem, selector );
  }

  // ----- matchesSelector ----- //

  var matchesSelector;

  if ( matchesMethod ) {
    // IE9 supports matchesSelector, but doesn't work on orphaned elems
    // check for that
    var div = document.createElement('div');
    var supportsOrphans = match( div, 'div' );
    matchesSelector = supportsOrphans ? match : matchChild;
  } else {
    matchesSelector = query;
  }

  // transport
  if ( typeof define === 'function' && define.amd ) {
    // AMD
    define( 'matches-selector/matches-selector',[],function() {
      return matchesSelector;
    });
  } else {
    // browser global
    window.matchesSelector = matchesSelector;
  }

})( this, Element.prototype );

/**
 * Outlayer Item
**/

( function( window ) {



// ----- get style ----- //

var defView = document.defaultView;

var getStyle = defView && defView.getComputedStyle ?
  function( elem ) {
    return defView.getComputedStyle( elem, null );
  } :
  function( elem ) {
    return elem.currentStyle;
  };


// extend objects
function extend( a, b ) {
  for ( var prop in b ) {
    a[ prop ] = b[ prop ];
  }
  return a;
}

function isEmptyObj( obj ) {
  for ( var prop in obj ) {
    return false;
  }
  prop = null;
  return true;
}

// http://jamesroberts.name/blog/2010/02/22/string-functions-for-javascript-trim-to-camel-case-to-dashed-and-to-underscore/
function toDash( str ) {
  return str.replace( /([A-Z])/g, function( $1 ){
    return '-' + $1.toLowerCase();
  });
}

// -------------------------- Outlayer definition -------------------------- //

function outlayerItemDefinition( EventEmitter, getSize, getStyleProperty ) {

// -------------------------- CSS3 support -------------------------- //

var transitionProperty = getStyleProperty('transition');
var transformProperty = getStyleProperty('transform');
var supportsCSS3 = transitionProperty && transformProperty;
var is3d = !!getStyleProperty('perspective');

var transitionEndEvent = {
  WebkitTransition: 'webkitTransitionEnd',
  MozTransition: 'transitionend',
  OTransition: 'otransitionend',
  transition: 'transitionend'
}[ transitionProperty ];

// properties that could have vendor prefix
var prefixableProperties = [
  'transform',
  'transition',
  'transitionDuration',
  'transitionProperty'
];

// cache all vendor properties
var vendorProperties = ( function() {
  var cache = {};
  for ( var i=0, len = prefixableProperties.length; i < len; i++ ) {
    var prop = prefixableProperties[i];
    var supportedProp = getStyleProperty( prop );
    if ( supportedProp && supportedProp !== prop ) {
      cache[ prop ] = supportedProp;
    }
  }
  return cache;
})();

// -------------------------- Item -------------------------- //

function Item( element, layout ) {
  if ( !element ) {
    return;
  }

  this.element = element;
  // parent layout class, i.e. Masonry, Isotope, or Packery
  this.layout = layout;
  this.position = {
    x: 0,
    y: 0
  };

  this._create();
}

// inherit EventEmitter
extend( Item.prototype, EventEmitter.prototype );

Item.prototype._create = function() {
  // transition objects
  this._transition = {
    ingProperties: {},
    clean: {},
    onEnd: {}
  };

  this.css({
    position: 'absolute'
  });
};

// trigger specified handler for event type
Item.prototype.handleEvent = function( event ) {
  var method = 'on' + event.type;
  if ( this[ method ] ) {
    this[ method ]( event );
  }
};

Item.prototype.getSize = function() {
  this.size = getSize( this.element );
};

/**
 * apply CSS styles to element
 * @param {Object} style
 */
Item.prototype.css = function( style ) {
  var elemStyle = this.element.style;

  for ( var prop in style ) {
    // use vendor property if available
    var supportedProp = vendorProperties[ prop ] || prop;
    elemStyle[ supportedProp ] = style[ prop ];
  }
};

 // measure position, and sets it
Item.prototype.getPosition = function() {
  var style = getStyle( this.element );
  var layoutOptions = this.layout.options;
  var isOriginLeft = layoutOptions.isOriginLeft;
  var isOriginTop = layoutOptions.isOriginTop;
  var x = parseInt( style[ isOriginLeft ? 'left' : 'right' ], 10 );
  var y = parseInt( style[ isOriginTop ? 'top' : 'bottom' ], 10 );

  // clean up 'auto' or other non-integer values
  x = isNaN( x ) ? 0 : x;
  y = isNaN( y ) ? 0 : y;
  // remove padding from measurement
  var layoutSize = this.layout.size;
  x -= isOriginLeft ? layoutSize.paddingLeft : layoutSize.paddingRight;
  y -= isOriginTop ? layoutSize.paddingTop : layoutSize.paddingBottom;

  this.position.x = x;
  this.position.y = y;
};

// set settled position, apply padding
Item.prototype.layoutPosition = function() {
  var layoutSize = this.layout.size;
  var layoutOptions = this.layout.options;
  var style = {};

  if ( layoutOptions.isOriginLeft ) {
    style.left = ( this.position.x + layoutSize.paddingLeft ) + 'px';
    // reset other property
    style.right = '';
  } else {
    style.right = ( this.position.x + layoutSize.paddingRight ) + 'px';
    style.left = '';
  }

  if ( layoutOptions.isOriginTop ) {
    style.top = ( this.position.y + layoutSize.paddingTop ) + 'px';
    style.bottom = '';
  } else {
    style.bottom = ( this.position.y + layoutSize.paddingBottom ) + 'px';
    style.top = '';
  }

  this.css( style );
  this.emitEvent( 'layout', [ this ] );
};


// transform translate function
var translate = is3d ?
  function( x, y ) {
    return 'translate3d(' + x + 'px, ' + y + 'px, 0)';
  } :
  function( x, y ) {
    return 'translate(' + x + 'px, ' + y + 'px)';
  };


Item.prototype._transitionTo = function( x, y ) {
  this.getPosition();
  // get current x & y from top/left
  var curX = this.position.x;
  var curY = this.position.y;

  var compareX = parseInt( x, 10 );
  var compareY = parseInt( y, 10 );
  var didNotMove = compareX === this.position.x && compareY === this.position.y;

  // save end position
  this.setPosition( x, y );

  // if did not move and not transitioning, just go to layout
  if ( didNotMove && !this.isTransitioning ) {
    this.layoutPosition();
    return;
  }

  var transX = x - curX;
  var transY = y - curY;
  var transitionStyle = {};
  // flip cooridinates if origin on right or bottom
  var layoutOptions = this.layout.options;
  transX = layoutOptions.isOriginLeft ? transX : -transX;
  transY = layoutOptions.isOriginTop ? transY : -transY;
  transitionStyle.transform = translate( transX, transY );

  this.transition({
    to: transitionStyle,
    onTransitionEnd: {
      transform: this.layoutPosition
    },
    isCleaning: true
  });
};

// non transition + transform support
Item.prototype.goTo = function( x, y ) {
  this.setPosition( x, y );
  this.layoutPosition();
};

// use transition and transforms if supported
Item.prototype.moveTo = supportsCSS3 ?
  Item.prototype._transitionTo : Item.prototype.goTo;

Item.prototype.setPosition = function( x, y ) {
  this.position.x = parseInt( x, 10 );
  this.position.y = parseInt( y, 10 );
};

// ----- transition ----- //

/**
 * @param {Object} style - CSS
 * @param {Function} onTransitionEnd
 */

// non transition, just trigger callback
Item.prototype._nonTransition = function( args ) {
  this.css( args.to );
  if ( args.isCleaning ) {
    this._removeStyles( args.to );
  }
  for ( var prop in args.onTransitionEnd ) {
    args.onTransitionEnd[ prop ].call( this );
  }
};

/**
 * proper transition
 * @param {Object} args - arguments
 *   @param {Object} to - style to transition to
 *   @param {Object} from - style to start transition from
 *   @param {Boolean} isCleaning - removes transition styles after transition
 *   @param {Function} onTransitionEnd - callback
 */
Item.prototype._transition = function( args ) {
  // redirect to nonTransition if no transition duration
  if ( !parseFloat( this.layout.options.transitionDuration ) ) {
    this._nonTransition( args );
    return;
  }

  var _transition = this._transition;
  // keep track of onTransitionEnd callback by css property
  for ( var prop in args.onTransitionEnd ) {
    _transition.onEnd[ prop ] = args.onTransitionEnd[ prop ];
  }
  // keep track of properties that are transitioning
  for ( prop in args.to ) {
    _transition.ingProperties[ prop ] = true;
    // keep track of properties to clean up when transition is done
    if ( args.isCleaning ) {
      _transition.clean[ prop ] = true;
    }
  }

  // set from styles
  if ( args.from ) {
    this.css( args.from );
    // force redraw. http://blog.alexmaccaw.com/css-transitions
    var h = this.element.offsetHeight;
    // hack for JSHint to hush about unused var
    h = null;
  }
  // enable transition
  this.enableTransition( args.to );
  // set styles that are transitioning
  this.css( args.to );

  this.isTransitioning = true;

};

var itemTransitionProperties = transformProperty && ( toDash( transformProperty ) +
  ',opacity' );

Item.prototype.enableTransition = function(/* style */) {
  // only enable if not already transitioning
  // bug in IE10 were re-setting transition style will prevent
  // transitionend event from triggering
  if ( this.isTransitioning ) {
    return;
  }

  // make transition: foo, bar, baz from style object
  // TODO uncomment this bit when IE10 bug is resolved
  // var transitionValue = [];
  // for ( var prop in style ) {
  //   // dash-ify camelCased properties like WebkitTransition
  //   transitionValue.push( toDash( prop ) );
  // }
  // enable transition styles
  // HACK always enable transform,opacity for IE10
  this.css({
    transitionProperty: itemTransitionProperties,
    transitionDuration: this.layout.options.transitionDuration
  });
  // listen for transition end event
  this.element.addEventListener( transitionEndEvent, this, false );
};

Item.prototype.transition = Item.prototype[ transitionProperty ? '_transition' : '_nonTransition' ];

// ----- events ----- //

Item.prototype.onwebkitTransitionEnd = function( event ) {
  this.ontransitionend( event );
};

Item.prototype.onotransitionend = function( event ) {
  this.ontransitionend( event );
};

// properties that I munge to make my life easier
var dashedVendorProperties = {
  '-webkit-transform': 'transform',
  '-moz-transform': 'transform',
  '-o-transform': 'transform'
};

Item.prototype.ontransitionend = function( event ) {
  // disregard bubbled events from children
  if ( event.target !== this.element ) {
    return;
  }
  var _transition = this._transition;
  // get property name of transitioned property, convert to prefix-free
  var propertyName = dashedVendorProperties[ event.propertyName ] || event.propertyName;

  // remove property that has completed transitioning
  delete _transition.ingProperties[ propertyName ];
  // check if any properties are still transitioning
  if ( isEmptyObj( _transition.ingProperties ) ) {
    // all properties have completed transitioning
    this.disableTransition();
  }
  // clean style
  if ( propertyName in _transition.clean ) {
    // clean up style
    this.element.style[ event.propertyName ] = '';
    delete _transition.clean[ propertyName ];
  }
  // trigger onTransitionEnd callback
  if ( propertyName in _transition.onEnd ) {
    var onTransitionEnd = _transition.onEnd[ propertyName ];
    onTransitionEnd.call( this );
    delete _transition.onEnd[ propertyName ];
  }

  this.emitEvent( 'transitionEnd', [ this ] );
};

Item.prototype.disableTransition = function() {
  this.removeTransitionStyles();
  this.element.removeEventListener( transitionEndEvent, this, false );
  this.isTransitioning = false;
};

/**
 * removes style property from element
 * @param {Object} style
**/
Item.prototype._removeStyles = function( style ) {
  // clean up transition styles
  var cleanStyle = {};
  for ( var prop in style ) {
    cleanStyle[ prop ] = '';
  }
  this.css( cleanStyle );
};

var cleanTransitionStyle = {
  transitionProperty: '',
  transitionDuration: ''
};

Item.prototype.removeTransitionStyles = function() {
  // remove transition
  this.css( cleanTransitionStyle );
};

// ----- show/hide/remove ----- //

// remove element from DOM
Item.prototype.removeElem = function() {
  this.element.parentNode.removeChild( this.element );
  this.emitEvent( 'remove', [ this ] );
};

Item.prototype.remove = function() {
  // just remove element if no transition support or no transition
  if ( !transitionProperty || !parseFloat( this.layout.options.transitionDuration ) ) {
    this.removeElem();
    return;
  }

  // start transition
  var _this = this;
  this.on( 'transitionEnd', function() {
    _this.removeElem();
    return true; // bind once
  });
  this.hide();
};

Item.prototype.reveal = function() {
  delete this.isHidden;
  // remove display: none
  this.css({ display: '' });

  var options = this.layout.options;
  this.transition({
    from: options.hiddenStyle,
    to: options.visibleStyle,
    isCleaning: true
  });
};

Item.prototype.hide = function() {
  // set flag
  this.isHidden = true;
  // remove display: none
  this.css({ display: '' });

  var options = this.layout.options;
  this.transition({
    from: options.visibleStyle,
    to: options.hiddenStyle,
    // keep hidden stuff hidden
    isCleaning: true,
    onTransitionEnd: {
      opacity: function() {
        this.css({ display: 'none' });
      }
    }
  });
};

Item.prototype.destroy = function() {
  this.css({
    position: '',
    left: '',
    right: '',
    top: '',
    bottom: '',
    transition: '',
    transform: ''
  });
};

return Item;

}

// -------------------------- transport -------------------------- //

if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'outlayer/item',[
      'eventEmitter/EventEmitter',
      'get-size/get-size',
      'get-style-property/get-style-property'
    ],
    outlayerItemDefinition );
} else {
  // browser global
  window.Outlayer = {};
  window.Outlayer.Item = outlayerItemDefinition(
    window.EventEmitter,
    window.getSize,
    window.getStyleProperty
  );
}

})( window );

/*!
 * Outlayer v1.1.8
 * the brains and guts of a layout library
 */

( function( window ) {



// ----- vars ----- //

var document = window.document;
var console = window.console;
var jQuery = window.jQuery;

var noop = function() {};

// -------------------------- helpers -------------------------- //

// extend objects
function extend( a, b ) {
  for ( var prop in b ) {
    a[ prop ] = b[ prop ];
  }
  return a;
}


var objToString = Object.prototype.toString;
function isArray( obj ) {
  return objToString.call( obj ) === '[object Array]';
}

// turn element or nodeList into an array
function makeArray( obj ) {
  var ary = [];
  if ( isArray( obj ) ) {
    // use object if already an array
    ary = obj;
  } else if ( obj && typeof obj.length === 'number' ) {
    // convert nodeList to array
    for ( var i=0, len = obj.length; i < len; i++ ) {
      ary.push( obj[i] );
    }
  } else {
    // array of single index
    ary.push( obj );
  }
  return ary;
}

// http://stackoverflow.com/a/384380/182183
var isElement = ( typeof HTMLElement === 'object' ) ?
  function isElementDOM2( obj ) {
    return obj instanceof HTMLElement;
  } :
  function isElementQuirky( obj ) {
    return obj && typeof obj === 'object' &&
      obj.nodeType === 1 && typeof obj.nodeName === 'string';
  };

// index of helper cause IE8
var indexOf = Array.prototype.indexOf ? function( ary, obj ) {
    return ary.indexOf( obj );
  } : function( ary, obj ) {
    for ( var i=0, len = ary.length; i < len; i++ ) {
      if ( ary[i] === obj ) {
        return i;
      }
    }
    return -1;
  };

function removeFrom( obj, ary ) {
  var index = indexOf( ary, obj );
  if ( index !== -1 ) {
    ary.splice( index, 1 );
  }
}

// http://jamesroberts.name/blog/2010/02/22/string-functions-for-javascript-trim-to-camel-case-to-dashed-and-to-underscore/
function toDashed( str ) {
  return str.replace( /(.)([A-Z])/g, function( match, $1, $2 ) {
    return $1 + '-' + $2;
  }).toLowerCase();
}


function outlayerDefinition( eventie, docReady, EventEmitter, getSize, matchesSelector, Item ) {

// -------------------------- Outlayer -------------------------- //

// globally unique identifiers
var GUID = 0;
// internal store of all Outlayer intances
var instances = {};


/**
 * @param {Element, String} element
 * @param {Object} options
 * @constructor
 */
function Outlayer( element, options ) {
  // use element as selector string
  if ( typeof element === 'string' ) {
    element = document.querySelector( element );
  }

  // bail out if not proper element
  if ( !element || !isElement( element ) ) {
    if ( console ) {
      console.error( 'Bad ' + this.settings.namespace + ' element: ' + element );
    }
    return;
  }

  this.element = element;

  // options
  this.options = extend( {}, this.options );
  this.option( options );

  // add id for Outlayer.getFromElement
  var id = ++GUID;
  this.element.outlayerGUID = id; // expando
  instances[ id ] = this; // associate via id

  // kick it off
  this._create();

  if ( this.options.isInitLayout ) {
    this.layout();
  }
}

// settings are for internal use only
Outlayer.prototype.settings = {
  namespace: 'outlayer',
  item: Item
};

// default options
Outlayer.prototype.options = {
  containerStyle: {
    position: 'relative'
  },
  isInitLayout: true,
  isOriginLeft: true,
  isOriginTop: true,
  isResizeBound: true,
  // item options
  transitionDuration: '0.4s',
  hiddenStyle: {
    opacity: 0,
    transform: 'scale(0.001)'
  },
  visibleStyle: {
    opacity: 1,
    transform: 'scale(1)'
  }
};

// inherit EventEmitter
extend( Outlayer.prototype, EventEmitter.prototype );

/**
 * set options
 * @param {Object} opts
 */
Outlayer.prototype.option = function( opts ) {
  extend( this.options, opts );
};

Outlayer.prototype._create = function() {
  // get items from children
  this.reloadItems();
  // elements that affect layout, but are not laid out
  this.stamps = [];
  this.stamp( this.options.stamp );
  // set container style
  extend( this.element.style, this.options.containerStyle );

  // bind resize method
  if ( this.options.isResizeBound ) {
    this.bindResize();
  }
};

// goes through all children again and gets bricks in proper order
Outlayer.prototype.reloadItems = function() {
  // collection of item elements
  this.items = this._itemize( this.element.children );
};


/**
 * turn elements into Outlayer.Items to be used in layout
 * @param {Array or NodeList or HTMLElement} elems
 * @returns {Array} items - collection of new Outlayer Items
 */
Outlayer.prototype._itemize = function( elems ) {

  var itemElems = this._filterFindItemElements( elems );
  var Item = this.settings.item;

  // create new Outlayer Items for collection
  var items = [];
  for ( var i=0, len = itemElems.length; i < len; i++ ) {
    var elem = itemElems[i];
    var item = new Item( elem, this );
    items.push( item );
  }

  return items;
};

/**
 * get item elements to be used in layout
 * @param {Array or NodeList or HTMLElement} elems
 * @returns {Array} items - item elements
 */
Outlayer.prototype._filterFindItemElements = function( elems ) {
  // make array of elems
  elems = makeArray( elems );
  var itemSelector = this.options.itemSelector;
  var itemElems = [];

  for ( var i=0, len = elems.length; i < len; i++ ) {
    var elem = elems[i];
    // check that elem is an actual element
    if ( !isElement( elem ) ) {
      continue;
    }
    // filter & find items if we have an item selector
    if ( itemSelector ) {
      // filter siblings
      if ( matchesSelector( elem, itemSelector ) ) {
        itemElems.push( elem );
      }
      // find children
      var childElems = elem.querySelectorAll( itemSelector );
      // concat childElems to filterFound array
      for ( var j=0, jLen = childElems.length; j < jLen; j++ ) {
        itemElems.push( childElems[j] );
      }
    } else {
      itemElems.push( elem );
    }
  }

  return itemElems;
};

/**
 * getter method for getting item elements
 * @returns {Array} elems - collection of item elements
 */
Outlayer.prototype.getItemElements = function() {
  var elems = [];
  for ( var i=0, len = this.items.length; i < len; i++ ) {
    elems.push( this.items[i].element );
  }
  return elems;
};

// ----- init & layout ----- //

/**
 * lays out all items
 */
Outlayer.prototype.layout = function() {
  this._resetLayout();
  this._manageStamps();

  // don't animate first layout
  var isInstant = this.options.isLayoutInstant !== undefined ?
    this.options.isLayoutInstant : !this._isLayoutInited;
  this.layoutItems( this.items, isInstant );

  // flag for initalized
  this._isLayoutInited = true;
};

// _init is alias for layout
Outlayer.prototype._init = Outlayer.prototype.layout;

/**
 * logic before any new layout
 */
Outlayer.prototype._resetLayout = function() {
  this.getSize();
};


Outlayer.prototype.getSize = function() {
  this.size = getSize( this.element );
};

/**
 * get measurement from option, for columnWidth, rowHeight, gutter
 * if option is String -> get element from selector string, & get size of element
 * if option is Element -> get size of element
 * else use option as a number
 *
 * @param {String} measurement
 * @param {String} size - width or height
 * @private
 */
Outlayer.prototype._getMeasurement = function( measurement, size ) {
  var option = this.options[ measurement ];
  var elem;
  if ( !option ) {
    // default to 0
    this[ measurement ] = 0;
  } else {
    if ( typeof option === 'string' ) {
      elem = this.element.querySelector( option );
    } else if ( isElement( option ) ) {
      elem = option;
    }
    // use size of element, if element
    this[ measurement ] = elem ? getSize( elem )[ size ] : option;
  }
};

/**
 * layout a collection of item elements
 * @api public
 */
Outlayer.prototype.layoutItems = function( items, isInstant ) {
  items = this._getItemsForLayout( items );

  this._layoutItems( items, isInstant );

  this._postLayout();
};

/**
 * get the items to be laid out
 * you may want to skip over some items
 * @param {Array} items
 * @returns {Array} items
 */
Outlayer.prototype._getItemsForLayout = function( items ) {
  var layoutItems = [];
  for ( var i=0, len = items.length; i < len; i++ ) {
    var item = items[i];
    if ( !item.isIgnored ) {
      layoutItems.push( item );
    }
  }
  return layoutItems;
};

/**
 * layout items
 * @param {Array} items
 * @param {Boolean} isInstant
 */
Outlayer.prototype._layoutItems = function( items, isInstant ) {
  if ( !items || !items.length ) {
    // no items, emit event with empty array
    this.emitEvent( 'layoutComplete', [ this, items ] );
    return;
  }

  // emit layoutComplete when done
  this._itemsOn( items, 'layout', function onItemsLayout() {
    this.emitEvent( 'layoutComplete', [ this, items ] );
  });

  var queue = [];

  for ( var i=0, len = items.length; i < len; i++ ) {
    var item = items[i];
    // get x/y object from method
    var position = this._getItemLayoutPosition( item );
    // enqueue
    position.item = item;
    position.isInstant = isInstant;
    queue.push( position );
  }

  this._processLayoutQueue( queue );
};

/**
 * get item layout position
 * @param {Outlayer.Item} item
 * @returns {Object} x and y position
 */
Outlayer.prototype._getItemLayoutPosition = function( /* item */ ) {
  return {
    x: 0,
    y: 0
  };
};

/**
 * iterate over array and position each item
 * Reason being - separating this logic prevents 'layout invalidation'
 * thx @paul_irish
 * @param {Array} queue
 */
Outlayer.prototype._processLayoutQueue = function( queue ) {
  for ( var i=0, len = queue.length; i < len; i++ ) {
    var obj = queue[i];
    this._positionItem( obj.item, obj.x, obj.y, obj.isInstant );
  }
};

/**
 * Sets position of item in DOM
 * @param {Outlayer.Item} item
 * @param {Number} x - horizontal position
 * @param {Number} y - vertical position
 * @param {Boolean} isInstant - disables transitions
 */
Outlayer.prototype._positionItem = function( item, x, y, isInstant ) {
  if ( isInstant ) {
    // if not transition, just set CSS
    item.goTo( x, y );
  } else {
    item.moveTo( x, y );
  }
};

/**
 * Any logic you want to do after each layout,
 * i.e. size the container
 */
Outlayer.prototype._postLayout = function() {
  var size = this._getContainerSize();
  if ( size ) {
    this._setContainerMeasure( size.width, true );
    this._setContainerMeasure( size.height, false );
  }
};

/**
 * @returns {Object} size
 *   @param {Number} width
 *   @param {Number} height
 */
Outlayer.prototype._getContainerSize = noop;

/**
 * @param {Number} measure - size of width or height
 * @param {Boolean} isWidth
 */
Outlayer.prototype._setContainerMeasure = function( measure, isWidth ) {
  if ( measure === undefined ) {
    return;
  }

  var elemSize = this.size;
  // add padding and border width if border box
  if ( elemSize.isBorderBox ) {
    measure += isWidth ? elemSize.paddingLeft + elemSize.paddingRight +
      elemSize.borderLeftWidth + elemSize.borderRightWidth :
      elemSize.paddingBottom + elemSize.paddingTop +
      elemSize.borderTopWidth + elemSize.borderBottomWidth;
  }

  measure = Math.max( measure, 0 );
  this.element.style[ isWidth ? 'width' : 'height' ] = measure + 'px';
};

/**
 * trigger a callback for a collection of items events
 * @param {Array} items - Outlayer.Items
 * @param {String} eventName
 * @param {Function} callback
 */
Outlayer.prototype._itemsOn = function( items, eventName, callback ) {
  var doneCount = 0;
  var count = items.length;
  // event callback
  var _this = this;
  function tick() {
    doneCount++;
    if ( doneCount === count ) {
      callback.call( _this );
    }
    return true; // bind once
  }
  // bind callback
  for ( var i=0, len = items.length; i < len; i++ ) {
    var item = items[i];
    item.on( eventName, tick );
  }
};

// -------------------------- ignore & stamps -------------------------- //


/**
 * keep item in collection, but do not lay it out
 * ignored items do not get skipped in layout
 * @param {Element} elem
 */
Outlayer.prototype.ignore = function( elem ) {
  var item = this.getItem( elem );
  if ( item ) {
    item.isIgnored = true;
  }
};

/**
 * return item to layout collection
 * @param {Element} elem
 */
Outlayer.prototype.unignore = function( elem ) {
  var item = this.getItem( elem );
  if ( item ) {
    delete item.isIgnored;
  }
};

/**
 * adds elements to stamps
 * @param {NodeList, Array, Element, or String} elems
 */
Outlayer.prototype.stamp = function( elems ) {
  elems = this._find( elems );
  if ( !elems ) {
    return;
  }

  this.stamps = this.stamps.concat( elems );
  // ignore
  for ( var i=0, len = elems.length; i < len; i++ ) {
    var elem = elems[i];
    this.ignore( elem );
  }
};

/**
 * removes elements to stamps
 * @param {NodeList, Array, or Element} elems
 */
Outlayer.prototype.unstamp = function( elems ) {
  elems = this._find( elems );
  if ( !elems ){
    return;
  }

  for ( var i=0, len = elems.length; i < len; i++ ) {
    var elem = elems[i];
    // filter out removed stamp elements
    removeFrom( elem, this.stamps );
    this.unignore( elem );
  }

};

/**
 * finds child elements
 * @param {NodeList, Array, Element, or String} elems
 * @returns {Array} elems
 */
Outlayer.prototype._find = function( elems ) {
  if ( !elems ) {
    return;
  }
  // if string, use argument as selector string
  if ( typeof elems === 'string' ) {
    elems = this.element.querySelectorAll( elems );
  }
  elems = makeArray( elems );
  return elems;
};

Outlayer.prototype._manageStamps = function() {
  if ( !this.stamps || !this.stamps.length ) {
    return;
  }

  this._getBoundingRect();

  for ( var i=0, len = this.stamps.length; i < len; i++ ) {
    var stamp = this.stamps[i];
    this._manageStamp( stamp );
  }
};

// update boundingLeft / Top
Outlayer.prototype._getBoundingRect = function() {
  // get bounding rect for container element
  var boundingRect = this.element.getBoundingClientRect();
  var size = this.size;
  this._boundingRect = {
    left: boundingRect.left + size.paddingLeft + size.borderLeftWidth,
    top: boundingRect.top + size.paddingTop + size.borderTopWidth,
    right: boundingRect.right - ( size.paddingRight + size.borderRightWidth ),
    bottom: boundingRect.bottom - ( size.paddingBottom + size.borderBottomWidth )
  };
};

/**
 * @param {Element} stamp
**/
Outlayer.prototype._manageStamp = noop;

/**
 * get x/y position of element relative to container element
 * @param {Element} elem
 * @returns {Object} offset - has left, top, right, bottom
 */
Outlayer.prototype._getElementOffset = function( elem ) {
  var boundingRect = elem.getBoundingClientRect();
  var thisRect = this._boundingRect;
  var size = getSize( elem );
  var offset = {
    left: boundingRect.left - thisRect.left - size.marginLeft,
    top: boundingRect.top - thisRect.top - size.marginTop,
    right: thisRect.right - boundingRect.right - size.marginRight,
    bottom: thisRect.bottom - boundingRect.bottom - size.marginBottom
  };
  return offset;
};

// -------------------------- resize -------------------------- //

// enable event handlers for listeners
// i.e. resize -> onresize
Outlayer.prototype.handleEvent = function( event ) {
  var method = 'on' + event.type;
  if ( this[ method ] ) {
    this[ method ]( event );
  }
};

/**
 * Bind layout to window resizing
 */
Outlayer.prototype.bindResize = function() {
  // bind just one listener
  if ( this.isResizeBound ) {
    return;
  }
  eventie.bind( window, 'resize', this );
  this.isResizeBound = true;
};

/**
 * Unbind layout to window resizing
 */
Outlayer.prototype.unbindResize = function() {
  eventie.unbind( window, 'resize', this );
  this.isResizeBound = false;
};

// original debounce by John Hann
// http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/

// this fires every resize
Outlayer.prototype.onresize = function() {
  if ( this.resizeTimeout ) {
    clearTimeout( this.resizeTimeout );
  }

  var _this = this;
  function delayed() {
    _this.resize();
    delete _this.resizeTimeout;
  }

  this.resizeTimeout = setTimeout( delayed, 100 );
};

// debounced, layout on resize
Outlayer.prototype.resize = function() {
  // don't trigger if size did not change
  var size = getSize( this.element );
  // check that this.size and size are there
  // IE8 triggers resize on body size change, so they might not be
  var hasSizes = this.size && size;
  if ( hasSizes && size.innerWidth === this.size.innerWidth ) {
    return;
  }

  this.layout();
};


// -------------------------- methods -------------------------- //

/**
 * add items to Outlayer instance
 * @param {Array or NodeList or Element} elems
 * @returns {Array} items - Outlayer.Items
**/
Outlayer.prototype.addItems = function( elems ) {
  var items = this._itemize( elems );
  // add items to collection
  if ( items.length ) {
    this.items = this.items.concat( items );
  }
  return items;
};

/**
 * Layout newly-appended item elements
 * @param {Array or NodeList or Element} elems
 */
Outlayer.prototype.appended = function( elems ) {
  var items = this.addItems( elems );
  if ( !items.length ) {
    return;
  }
  // layout and reveal just the new items
  this.layoutItems( items, true );
  this.reveal( items );
};

/**
 * Layout prepended elements
 * @param {Array or NodeList or Element} elems
 */
Outlayer.prototype.prepended = function( elems ) {
  var items = this._itemize( elems );
  if ( !items.length ) {
    return;
  }
  // add items to beginning of collection
  var previousItems = this.items.slice(0);
  this.items = items.concat( previousItems );
  // start new layout
  this._resetLayout();
  this._manageStamps();
  // layout new stuff without transition
  this.layoutItems( items, true );
  this.reveal( items );
  // layout previous items
  this.layoutItems( previousItems );
};

/**
 * reveal a collection of items
 * @param {Array of Outlayer.Items} items
 */
Outlayer.prototype.reveal = function( items ) {
  if ( !items || !items.length ) {
    return;
  }
  for ( var i=0, len = items.length; i < len; i++ ) {
    var item = items[i];
    item.reveal();
  }
};

/**
 * hide a collection of items
 * @param {Array of Outlayer.Items} items
 */
Outlayer.prototype.hide = function( items ) {
  if ( !items || !items.length ) {
    return;
  }
  for ( var i=0, len = items.length; i < len; i++ ) {
    var item = items[i];
    item.hide();
  }
};

/**
 * get Outlayer.Item, given an Element
 * @param {Element} elem
 * @param {Function} callback
 * @returns {Outlayer.Item} item
 */
Outlayer.prototype.getItem = function( elem ) {
  // loop through items to get the one that matches
  for ( var i=0, len = this.items.length; i < len; i++ ) {
    var item = this.items[i];
    if ( item.element === elem ) {
      // return item
      return item;
    }
  }
};

/**
 * get collection of Outlayer.Items, given Elements
 * @param {Array} elems
 * @returns {Array} items - Outlayer.Items
 */
Outlayer.prototype.getItems = function( elems ) {
  if ( !elems || !elems.length ) {
    return;
  }
  var items = [];
  for ( var i=0, len = elems.length; i < len; i++ ) {
    var elem = elems[i];
    var item = this.getItem( elem );
    if ( item ) {
      items.push( item );
    }
  }

  return items;
};

/**
 * remove element(s) from instance and DOM
 * @param {Array or NodeList or Element} elems
 */
Outlayer.prototype.remove = function( elems ) {
  elems = makeArray( elems );

  var removeItems = this.getItems( elems );
  // bail if no items to remove
  if ( !removeItems || !removeItems.length ) {
    return;
  }

  this._itemsOn( removeItems, 'remove', function() {
    this.emitEvent( 'removeComplete', [ this, removeItems ] );
  });

  for ( var i=0, len = removeItems.length; i < len; i++ ) {
    var item = removeItems[i];
    item.remove();
    // remove item from collection
    removeFrom( item, this.items );
  }
};

// ----- destroy ----- //

// remove and disable Outlayer instance
Outlayer.prototype.destroy = function() {
  // clean up dynamic styles
  var style = this.element.style;
  style.height = '';
  style.position = '';
  style.width = '';
  // destroy items
  for ( var i=0, len = this.items.length; i < len; i++ ) {
    var item = this.items[i];
    item.destroy();
  }

  this.unbindResize();

  delete this.element.outlayerGUID;
  // remove data for jQuery
  if ( jQuery ) {
    jQuery.removeData( this.element, this.settings.namespace );
  }

};

// -------------------------- data -------------------------- //

/**
 * get Outlayer instance from element
 * @param {Element} elem
 * @returns {Outlayer}
 */
Outlayer.data = function( elem ) {
  var id = elem && elem.outlayerGUID;
  return id && instances[ id ];
};

// --------------------------  -------------------------- //

// copy an object on the Outlayer prototype
// used in options and settings
function copyOutlayerProto( obj, property ) {
  obj.prototype[ property ] = extend( {}, Outlayer.prototype[ property ] );
}

// -------------------------- create Outlayer class -------------------------- //

/**
 * create a layout class
 * @param {String} namespace
 */
Outlayer.create = function( namespace, options ) {
  // sub-class Outlayer
  function Layout() {
    Outlayer.apply( this, arguments );
  }

  extend( Layout.prototype, Outlayer.prototype );

  copyOutlayerProto( Layout, 'options' );
  copyOutlayerProto( Layout, 'settings' );

  extend( Layout.prototype.options, options );

  Layout.prototype.settings.namespace = namespace;

  Layout.data = Outlayer.data;

  // sub-class Item
  Layout.Item = function LayoutItem() {
    Item.apply( this, arguments );
  };

  Layout.Item.prototype = new Item();

  Layout.prototype.settings.item = Layout.Item;

  // -------------------------- declarative -------------------------- //

  /**
   * allow user to initialize Outlayer via .js-namespace class
   * options are parsed from data-namespace-option attribute
   */
  docReady( function() {
    var dashedNamespace = toDashed( namespace );
    var elems = document.querySelectorAll( '.js-' + dashedNamespace );
    var dataAttr = 'data-' + dashedNamespace + '-options';

    for ( var i=0, len = elems.length; i < len; i++ ) {
      var elem = elems[i];
      var attr = elem.getAttribute( dataAttr );
      var options;
      try {
        options = attr && JSON.parse( attr );
      } catch ( error ) {
        // log error, do not initialize
        if ( console ) {
          console.error( 'Error parsing ' + dataAttr + ' on ' +
            elem.nodeName.toLowerCase() + ( elem.id ? '#' + elem.id : '' ) + ': ' +
            error );
        }
        continue;
      }
      // initialize
      var instance = new Layout( elem, options );
      // make available via $().data('layoutname')
      if ( jQuery ) {
        jQuery.data( elem, namespace, instance );
      }
    }
  });

  // -------------------------- jQuery bridge -------------------------- //

  // make into jQuery plugin
  if ( jQuery && jQuery.bridget ) {
    jQuery.bridget( namespace, Layout );
  }

  return Layout;
};

// ----- fin ----- //

// back in global
Outlayer.Item = Item;

return Outlayer;

}

// -------------------------- transport -------------------------- //

if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'outlayer/outlayer',[
      'eventie/eventie',
      'doc-ready/doc-ready',
      'eventEmitter/EventEmitter',
      'get-size/get-size',
      'matches-selector/matches-selector',
      './item'
    ],
    outlayerDefinition );
} else {
  // browser global
  window.Outlayer = outlayerDefinition(
    window.eventie,
    window.docReady,
    window.EventEmitter,
    window.getSize,
    window.matchesSelector,
    window.Outlayer.Item
  );
}

})( window );

/*!
 * Masonry v3.1.2
 * Cascading grid layout library
 * http://masonry.desandro.com
 * MIT License
 * by David DeSandro
 */

( function( window ) {



// -------------------------- helpers -------------------------- //

var indexOf = Array.prototype.indexOf ?
  function( items, value ) {
    return items.indexOf( value );
  } :
  function ( items, value ) {
    for ( var i=0, len = items.length; i < len; i++ ) {
      var item = items[i];
      if ( item === value ) {
        return i;
      }
    }
    return -1;
  };

// -------------------------- masonryDefinition -------------------------- //

// used for AMD definition and requires
function masonryDefinition( Outlayer, getSize ) {
  // create an Outlayer layout class
  var Masonry = Outlayer.create('masonry');

  Masonry.prototype._resetLayout = function() {
    this.getSize();
    this._getMeasurement( 'columnWidth', 'outerWidth' );
    this._getMeasurement( 'gutter', 'outerWidth' );
    this.measureColumns();

    // reset column Y
    var i = this.cols;
    this.colYs = [];
    while (i--) {
      this.colYs.push( 0 );
    }

    this.maxY = 0;
  };

  Masonry.prototype.measureColumns = function() {
    this.getContainerWidth();
    // if columnWidth is 0, default to outerWidth of first item
    if ( !this.columnWidth ) {
      var firstItem = this.items[0];
      var firstItemElem = firstItem && firstItem.element;
      // columnWidth fall back to item of first element
      this.columnWidth = firstItemElem && getSize( firstItemElem ).outerWidth ||
        // if first elem has no width, default to size of container
        this.containerWidth;
    }

    this.columnWidth += this.gutter;

    this.cols = Math.floor( ( this.containerWidth + this.gutter ) / this.columnWidth );
    this.cols = Math.max( this.cols, 1 );
  };

  Masonry.prototype.getContainerWidth = function() {
    // container is parent if fit width
    var container = this.options.isFitWidth ? this.element.parentNode : this.element;
    // check that this.size and size are there
    // IE8 triggers resize on body size change, so they might not be
    var size = getSize( container );
    this.containerWidth = size && size.innerWidth;
  };

  Masonry.prototype._getItemLayoutPosition = function( item ) {
    item.getSize();
    // how many columns does this brick span
    var colSpan = Math.ceil( item.size.outerWidth / this.columnWidth );
    colSpan = Math.min( colSpan, this.cols );

    var colGroup = this._getColGroup( colSpan );
    // get the minimum Y value from the columns
    var minimumY = Math.min.apply( Math, colGroup );
    var shortColIndex = indexOf( colGroup, minimumY );

    // position the brick
    var position = {
      x: this.columnWidth * shortColIndex,
      y: minimumY
    };

    // apply setHeight to necessary columns
    var setHeight = minimumY + item.size.outerHeight;
    var setSpan = this.cols + 1 - colGroup.length;
    for ( var i = 0; i < setSpan; i++ ) {
      this.colYs[ shortColIndex + i ] = setHeight;
    }

    return position;
  };

  /**
   * @param {Number} colSpan - number of columns the element spans
   * @returns {Array} colGroup
   */
  Masonry.prototype._getColGroup = function( colSpan ) {
    if ( colSpan < 2 ) {
      // if brick spans only one column, use all the column Ys
      return this.colYs;
    }

    var colGroup = [];
    // how many different places could this brick fit horizontally
    var groupCount = this.cols + 1 - colSpan;
    // for each group potential horizontal position
    for ( var i = 0; i < groupCount; i++ ) {
      // make an array of colY values for that one group
      var groupColYs = this.colYs.slice( i, i + colSpan );
      // and get the max value of the array
      colGroup[i] = Math.max.apply( Math, groupColYs );
    }
    return colGroup;
  };

  Masonry.prototype._manageStamp = function( stamp ) {
    var stampSize = getSize( stamp );
    var offset = this._getElementOffset( stamp );
    // get the columns that this stamp affects
    var firstX = this.options.isOriginLeft ? offset.left : offset.right;
    var lastX = firstX + stampSize.outerWidth;
    var firstCol = Math.floor( firstX / this.columnWidth );
    firstCol = Math.max( 0, firstCol );
    var lastCol = Math.floor( lastX / this.columnWidth );
    lastCol = Math.min( this.cols - 1, lastCol );
    // set colYs to bottom of the stamp
    var stampMaxY = ( this.options.isOriginTop ? offset.top : offset.bottom ) +
      stampSize.outerHeight;
    for ( var i = firstCol; i <= lastCol; i++ ) {
      this.colYs[i] = Math.max( stampMaxY, this.colYs[i] );
    }
  };

  Masonry.prototype._getContainerSize = function() {
    this.maxY = Math.max.apply( Math, this.colYs );
    var size = {
      height: this.maxY
    };

    if ( this.options.isFitWidth ) {
      size.width = this._getContainerFitWidth();
    }

    return size;
  };

  Masonry.prototype._getContainerFitWidth = function() {
    var unusedCols = 0;
    // count unused columns
    var i = this.cols;
    while ( --i ) {
      if ( this.colYs[i] !== 0 ) {
        break;
      }
      unusedCols++;
    }
    // fit container to columns that have been used
    return ( this.cols - unusedCols ) * this.columnWidth - this.gutter;
  };

  // debounced, layout on resize
  // HEADS UP this overwrites Outlayer.resize
  // Any changes in Outlayer.resize need to be manually added here
  Masonry.prototype.resize = function() {
    // don't trigger if size did not change
    var previousWidth = this.containerWidth;
    this.getContainerWidth();
    if ( previousWidth === this.containerWidth ) {
      return;
    }

    this.layout();
  };

  return Masonry;
}

// -------------------------- transport -------------------------- //

if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( 'masonry/masonry',[
      'outlayer/outlayer',
      'get-size/get-size'
    ],
    masonryDefinition );
} else {
  // browser global
  window.Masonry = masonryDefinition(
    window.Outlayer,
    window.getSize
  );
}

})( window );

define('jquery-loader',[
  'jquery',
  'masonry/masonry'
  ], function($) {
    return $;
});
/* vim: set tabstop=4 softtabstop=4 shiftwidth=4 noexpandtab: */
/**
 * Backbone-relational.js 0.8.5
 * (c) 2011-2013 Paul Uithol and contributors (https://github.com/PaulUithol/Backbone-relational/graphs/contributors)
 * 
 * Backbone-relational may be freely distributed under the MIT license; see the accompanying LICENSE.txt.
 * For details and documentation: https://github.com/PaulUithol/Backbone-relational.
 * Depends on Backbone (and thus on Underscore as well): https://github.com/documentcloud/backbone.
 */
( function( undefined ) {
	

	/**
	 * CommonJS shim
	 **/
	var _, Backbone, exports;
	if ( typeof window === 'undefined' ) {
		_ = require( 'underscore' );
		Backbone = require( 'backbone' );
		exports = module.exports = Backbone;
	}
	else {
		_ = window._;
		Backbone = window.Backbone;
		exports = window;
	}

	Backbone.Relational = {
		showWarnings: true
	};

	/**
	 * Semaphore mixin; can be used as both binary and counting.
	 **/
	Backbone.Semaphore = {
		_permitsAvailable: null,
		_permitsUsed: 0,

		acquire: function() {
			if ( this._permitsAvailable && this._permitsUsed >= this._permitsAvailable ) {
				throw new Error( 'Max permits acquired' );
			}
			else {
				this._permitsUsed++;
			}
		},

		release: function() {
			if ( this._permitsUsed === 0 ) {
				throw new Error( 'All permits released' );
			}
			else {
				this._permitsUsed--;
			}
		},

		isLocked: function() {
			return this._permitsUsed > 0;
		},

		setAvailablePermits: function( amount ) {
			if ( this._permitsUsed > amount ) {
				throw new Error( 'Available permits cannot be less than used permits' );
			}
			this._permitsAvailable = amount;
		}
	};

	/**
	 * A BlockingQueue that accumulates items while blocked (via 'block'),
	 * and processes them when unblocked (via 'unblock').
	 * Process can also be called manually (via 'process').
	 */
	Backbone.BlockingQueue = function() {
		this._queue = [];
	};
	_.extend( Backbone.BlockingQueue.prototype, Backbone.Semaphore, {
		_queue: null,

		add: function( func ) {
			if ( this.isBlocked() ) {
				this._queue.push( func );
			}
			else {
				func();
			}
		},

		process: function() {
			while ( this._queue && this._queue.length ) {
				this._queue.shift()();
			}
		},

		block: function() {
			this.acquire();
		},

		unblock: function() {
			this.release();
			if ( !this.isBlocked() ) {
				this.process();
			}
		},

		isBlocked: function() {
			return this.isLocked();
		}
	});
	/**
	 * Global event queue. Accumulates external events ('add:<key>', 'remove:<key>' and 'change:<key>')
	 * until the top-level object is fully initialized (see 'Backbone.RelationalModel').
	 */
	Backbone.Relational.eventQueue = new Backbone.BlockingQueue();

	/**
	 * Backbone.Store keeps track of all created (and destruction of) Backbone.RelationalModel.
	 * Handles lookup for relations.
	 */
	Backbone.Store = function() {
		this._collections = [];
		this._reverseRelations = [];
		this._orphanRelations = [];
		this._subModels = [];
		this._modelScopes = [ exports ];
	};
	_.extend( Backbone.Store.prototype, Backbone.Events, {
		/**
		 * Create a new `Relation`.
		 * @param {Backbone.RelationalModel} [model]
		 * @param {Object} relation
		 * @param {Object} [options]
		 */
		initializeRelation: function( model, relation, options ) {
			var type = !_.isString( relation.type ) ? relation.type : Backbone[ relation.type ] || this.getObjectByName( relation.type );
			if ( type && type.prototype instanceof Backbone.Relation ) {
				new type( model, relation, options ); // Also pushes the new Relation into `model._relations`
			}
			else {
				Backbone.Relational.showWarnings && typeof console !== 'undefined' && console.warn( 'Relation=%o; missing or invalid relation type!', relation );
			}
		},

		/**
		 * Add a scope for `getObjectByName` to look for model types by name.
		 * @param {Object} scope
		 */
		addModelScope: function( scope ) {
			this._modelScopes.push( scope );
		},

		/**
		 * Remove a scope.
		 * @param {Object} scope
		 */
		removeModelScope: function( scope ) {
			this._modelScopes = _.without( this._modelScopes, scope );
		},

		/**
		 * Add a set of subModelTypes to the store, that can be used to resolve the '_superModel'
		 * for a model later in 'setupSuperModel'.
		 *
		 * @param {Backbone.RelationalModel} subModelTypes
		 * @param {Backbone.RelationalModel} superModelType
		 */
		addSubModels: function( subModelTypes, superModelType ) {
			this._subModels.push({
				'superModelType': superModelType,
				'subModels': subModelTypes
			});
		},

		/**
		 * Check if the given modelType is registered as another model's subModel. If so, add it to the super model's
		 * '_subModels', and set the modelType's '_superModel', '_subModelTypeName', and '_subModelTypeAttribute'.
		 *
		 * @param {Backbone.RelationalModel} modelType
		 */
		setupSuperModel: function( modelType ) {
			_.find( this._subModels, function( subModelDef ) {
				return _.find( subModelDef.subModels || [], function( subModelTypeName, typeValue ) {
					var subModelType = this.getObjectByName( subModelTypeName );

					if ( modelType === subModelType ) {
						// Set 'modelType' as a child of the found superModel
						subModelDef.superModelType._subModels[ typeValue ] = modelType;

						// Set '_superModel', '_subModelTypeValue', and '_subModelTypeAttribute' on 'modelType'.
						modelType._superModel = subModelDef.superModelType;
						modelType._subModelTypeValue = typeValue;
						modelType._subModelTypeAttribute = subModelDef.superModelType.prototype.subModelTypeAttribute;
						return true;
					}
				}, this );
			}, this );
		},

		/**
		 * Add a reverse relation. Is added to the 'relations' property on model's prototype, and to
		 * existing instances of 'model' in the store as well.
		 * @param {Object} relation
		 * @param {Backbone.RelationalModel} relation.model
		 * @param {String} relation.type
		 * @param {String} relation.key
		 * @param {String|Object} relation.relatedModel
		 */
		addReverseRelation: function( relation ) {
			var exists = _.any( this._reverseRelations, function( rel ) {
				return _.all( relation || [], function( val, key ) {
					return val === rel[ key ];
				});
			});
			
			if ( !exists && relation.model && relation.type ) {
				this._reverseRelations.push( relation );
				this._addRelation( relation.model, relation );
				this.retroFitRelation( relation );
			}
		},

		/**
		 * Deposit a `relation` for which the `relatedModel` can't be resolved at the moment.
		 *
		 * @param {Object} relation
		 */
		addOrphanRelation: function( relation ) {
			var exists = _.any( this._orphanRelations, function( rel ) {
				return _.all( relation || [], function( val, key ) {
					return val === rel[ key ];
				});
			});

			if ( !exists && relation.model && relation.type ) {
				this._orphanRelations.push( relation );
			}
		},

		/**
		 * Try to initialize any `_orphanRelation`s
		 */
		processOrphanRelations: function() {
			// Make sure to operate on a copy since we're removing while iterating
			_.each( this._orphanRelations.slice( 0 ), function( rel ) {
				var relatedModel = Backbone.Relational.store.getObjectByName( rel.relatedModel );
				if ( relatedModel ) {
					this.initializeRelation( null, rel );
					this._orphanRelations = _.without( this._orphanRelations, rel );
				}
			}, this );
		},

		/**
		 *
		 * @param {Backbone.RelationalModel.constructor} type
		 * @param {Object} relation
		 * @private
		 */
		_addRelation: function( type, relation ) {
			if ( !type.prototype.relations ) {
				type.prototype.relations = [];
			}
			type.prototype.relations.push( relation );

			_.each( type._subModels || [], function( subModel ) {
				this._addRelation( subModel, relation );
			}, this );
		},

		/**
		 * Add a 'relation' to all existing instances of 'relation.model' in the store
		 * @param {Object} relation
		 */
		retroFitRelation: function( relation ) {
			var coll = this.getCollection( relation.model, false );
			coll && coll.each( function( model ) {
				if ( !( model instanceof relation.model ) ) {
					return;
				}

				new relation.type( model, relation );
			}, this );
		},

		/**
		 * Find the Store's collection for a certain type of model.
		 * @param {Backbone.RelationalModel} type
		 * @param {Boolean} [create=true] Should a collection be created if none is found?
		 * @return {Backbone.Collection} A collection if found (or applicable for 'model'), or null
		 */
		getCollection: function( type, create ) {
			if ( type instanceof Backbone.RelationalModel ) {
				type = type.constructor;
			}
			
			var rootModel = type;
			while ( rootModel._superModel ) {
				rootModel = rootModel._superModel;
			}
			
			var coll = _.findWhere( this._collections, { model: rootModel } );
			
			if ( !coll && create !== false ) {
				coll = this._createCollection( rootModel );
			}
			
			return coll;
		},

		/**
		 * Find a model type on one of the modelScopes by name. Names are split on dots.
		 * @param {String} name
		 * @return {Object}
		 */
		getObjectByName: function( name ) {
			var parts = name.split( '.' ),
				type = null;

			_.find( this._modelScopes, function( scope ) {
				type = _.reduce( parts || [], function( memo, val ) {
					return memo ? memo[ val ] : undefined;
				}, scope );

				if ( type && type !== scope ) {
					return true;
				}
			}, this );

			return type;
		},

		_createCollection: function( type ) {
			var coll;
			
			// If 'type' is an instance, take its constructor
			if ( type instanceof Backbone.RelationalModel ) {
				type = type.constructor;
			}
			
			// Type should inherit from Backbone.RelationalModel.
			if ( type.prototype instanceof Backbone.RelationalModel ) {
				coll = new Backbone.Collection();
				coll.model = type;
				
				this._collections.push( coll );
			}
			
			return coll;
		},

		/**
		 * Find the attribute that is to be used as the `id` on a given object
		 * @param type
		 * @param {String|Number|Object|Backbone.RelationalModel} item
		 * @return {String|Number}
		 */
		resolveIdForItem: function( type, item ) {
			var id = _.isString( item ) || _.isNumber( item ) ? item : null;

			if ( id === null ) {
				if ( item instanceof Backbone.RelationalModel ) {
					id = item.id;
				}
				else if ( _.isObject( item ) ) {
					id = item[ type.prototype.idAttribute ];
				}
			}

			// Make all falsy values `null` (except for 0, which could be an id.. see '/issues/179')
			if ( !id && id !== 0 ) {
				id = null;
			}

			return id;
		},

		/**
		 * Find a specific model of a certain `type` in the store
		 * @param type
		 * @param {String|Number|Object|Backbone.RelationalModel} item
		 */
		find: function( type, item ) {
			var id = this.resolveIdForItem( type, item );
			var coll = this.getCollection( type );
			
			// Because the found object could be of any of the type's superModel
			// types, only return it if it's actually of the type asked for.
			if ( coll ) {
				var obj = coll.get( id );

				if ( obj instanceof type ) {
					return obj;
				}
			}

			return null;
		},

		/**
		 * Add a 'model' to its appropriate collection. Retain the original contents of 'model.collection'.
		 * @param {Backbone.RelationalModel} model
		 */
		register: function( model ) {
			var coll = this.getCollection( model );

			if ( coll ) {
				var modelColl = model.collection;
				coll.add( model );
				this.listenTo( model, 'destroy', this.unregister, this );
				model.collection = modelColl;
			}
		},

		/**
		 * Check if the given model may use the given `id`
		 * @param model
		 * @param [id]
		 */
		checkId: function( model, id ) {
			var coll = this.getCollection( model ),
				duplicate = coll && coll.get( id );

			if ( duplicate && model !== duplicate ) {
				if ( Backbone.Relational.showWarnings && typeof console !== 'undefined' ) {
					console.warn( 'Duplicate id! Old RelationalModel=%o, new RelationalModel=%o', duplicate, model );
				}

				throw new Error( "Cannot instantiate more than one Backbone.RelationalModel with the same id per type!" );
			}
		},

		/**
		 * Explicitly update a model's id in its store collection
		 * @param {Backbone.RelationalModel} model
		 */
		update: function( model ) {
			var coll = this.getCollection( model );
			// This triggers updating the lookup indices kept in a collection
			coll._onModelEvent( 'change:' + model.idAttribute, model, coll );

			// Trigger an event on model so related models (having the model's new id in their keyContents) can add it.
			model.trigger( 'relational:change:id', model, coll );
		},

		/**
		 * Remove a 'model' from the store.
		 * @param {Backbone.RelationalModel} model
		 */
		unregister: function( model ) {
			this.stopListening( model, 'destroy', this.unregister );
			var coll = this.getCollection( model );
			coll && coll.remove( model );
		},

		/**
		 * Reset the `store` to it's original state. The `reverseRelations` are kept though, since attempting to
		 * re-initialize these on models would lead to a large amount of warnings.
		 */
		reset: function() {
			this.stopListening();
			this._collections = [];
			this._subModels = [];
			this._modelScopes = [ exports ];
		}
	});
	Backbone.Relational.store = new Backbone.Store();

	/**
	 * The main Relation class, from which 'HasOne' and 'HasMany' inherit. Internally, 'relational:<key>' events
	 * are used to regulate addition and removal of models from relations.
	 *
	 * @param {Backbone.RelationalModel} [instance] Model that this relation is created for. If no model is supplied,
	 *      Relation just tries to instantiate it's `reverseRelation` if specified, and bails out after that.
	 * @param {Object} options
	 * @param {string} options.key
	 * @param {Backbone.RelationalModel.constructor} options.relatedModel
	 * @param {Boolean|String} [options.includeInJSON=true] Serialize the given attribute for related model(s)' in toJSON, or just their ids.
	 * @param {Boolean} [options.createModels=true] Create objects from the contents of keys if the object is not found in Backbone.store.
	 * @param {Object} [options.reverseRelation] Specify a bi-directional relation. If provided, Relation will reciprocate
	 *    the relation to the 'relatedModel'. Required and optional properties match 'options', except that it also needs
	 *    {Backbone.Relation|String} type ('HasOne' or 'HasMany').
	 * @param {Object} opts
	 */
	Backbone.Relation = function( instance, options, opts ) {
		this.instance = instance;
		// Make sure 'options' is sane, and fill with defaults from subclasses and this object's prototype
		options = _.isObject( options ) ? options : {};
		this.reverseRelation = _.defaults( options.reverseRelation || {}, this.options.reverseRelation );
		this.options = _.defaults( options, this.options, Backbone.Relation.prototype.options );

		this.reverseRelation.type = !_.isString( this.reverseRelation.type ) ? this.reverseRelation.type :
			Backbone[ this.reverseRelation.type ] || Backbone.Relational.store.getObjectByName( this.reverseRelation.type );

		this.key = this.options.key;
		this.keySource = this.options.keySource || this.key;
		this.keyDestination = this.options.keyDestination || this.keySource || this.key;

		this.model = this.options.model || this.instance.constructor;
		this.relatedModel = this.options.relatedModel;
		if ( _.isString( this.relatedModel ) ) {
			this.relatedModel = Backbone.Relational.store.getObjectByName( this.relatedModel );
		}

		if ( !this.checkPreconditions() ) {
			return;
		}

		// Add the reverse relation on 'relatedModel' to the store's reverseRelations
		if ( !this.options.isAutoRelation && this.reverseRelation.type && this.reverseRelation.key ) {
			Backbone.Relational.store.addReverseRelation( _.defaults( {
					isAutoRelation: true,
					model: this.relatedModel,
					relatedModel: this.model,
					reverseRelation: this.options // current relation is the 'reverseRelation' for its own reverseRelation
				},
				this.reverseRelation // Take further properties from this.reverseRelation (type, key, etc.)
			) );
		}

		if ( instance ) {
			var contentKey = this.keySource;
			if ( contentKey !== this.key && typeof this.instance.get( this.key ) === 'object' ) {
				contentKey = this.key;
			}

			this.setKeyContents( this.instance.get( contentKey ) );
			this.relatedCollection = Backbone.Relational.store.getCollection( this.relatedModel );

			// Explicitly clear 'keySource', to prevent a leaky abstraction if 'keySource' differs from 'key'.
			if ( this.keySource !== this.key ) {
				this.instance.unset( this.keySource, { silent: true } );
			}

			// Add this Relation to instance._relations
			this.instance._relations[ this.key ] = this;

			this.initialize( opts );

			if ( this.options.autoFetch ) {
				this.instance.fetchRelated( this.key, _.isObject( this.options.autoFetch ) ? this.options.autoFetch : {} );
			}

			// When 'relatedModel' are created or destroyed, check if it affects this relation.
			this.listenTo( this.instance, 'destroy', this.destroy )
				.listenTo( this.relatedCollection, 'relational:add relational:change:id', this.tryAddRelated )
				.listenTo( this.relatedCollection, 'relational:remove', this.removeRelated )
		}
	};
	// Fix inheritance :\
	Backbone.Relation.extend = Backbone.Model.extend;
	// Set up all inheritable **Backbone.Relation** properties and methods.
	_.extend( Backbone.Relation.prototype, Backbone.Events, Backbone.Semaphore, {
		options: {
			createModels: true,
			includeInJSON: true,
			isAutoRelation: false,
			autoFetch: false,
			parse: false
		},

		instance: null,
		key: null,
		keyContents: null,
		relatedModel: null,
		relatedCollection: null,
		reverseRelation: null,
		related: null,

		/**
		 * Check several pre-conditions.
		 * @return {Boolean} True if pre-conditions are satisfied, false if they're not.
		 */
		checkPreconditions: function() {
			var i = this.instance,
				k = this.key,
				m = this.model,
				rm = this.relatedModel,
				warn = Backbone.Relational.showWarnings && typeof console !== 'undefined';

			if ( !m || !k || !rm ) {
				warn && console.warn( 'Relation=%o: missing model, key or relatedModel (%o, %o, %o).', this, m, k, rm );
				return false;
			}
			// Check if the type in 'model' inherits from Backbone.RelationalModel
			if ( !( m.prototype instanceof Backbone.RelationalModel ) ) {
				warn && console.warn( 'Relation=%o: model does not inherit from Backbone.RelationalModel (%o).', this, i );
				return false;
			}
			// Check if the type in 'relatedModel' inherits from Backbone.RelationalModel
			if ( !( rm.prototype instanceof Backbone.RelationalModel ) ) {
				warn && console.warn( 'Relation=%o: relatedModel does not inherit from Backbone.RelationalModel (%o).', this, rm );
				return false;
			}
			// Check if this is not a HasMany, and the reverse relation is HasMany as well
			if ( this instanceof Backbone.HasMany && this.reverseRelation.type === Backbone.HasMany ) {
				warn && console.warn( 'Relation=%o: relation is a HasMany, and the reverseRelation is HasMany as well.', this );
				return false;
			}
			// Check if we're not attempting to create a relationship on a `key` that's already used.
			if ( i && _.keys( i._relations ).length ) {
				var existing = _.find( i._relations, function( rel ) {
					return rel.key === k;
				}, this );

				if ( existing ) {
					warn && console.warn( 'Cannot create relation=%o on %o for model=%o: already taken by relation=%o.',
						this, k, i, existing );
					return false;
				}
			}

			return true;
		},

		/**
		 * Set the related model(s) for this relation
		 * @param {Backbone.Model|Backbone.Collection} related
		 */
		setRelated: function( related ) {
			this.related = related;

			this.instance.acquire();
			this.instance.attributes[ this.key ] = related;
			this.instance.release();
		},

		/**
		 * Determine if a relation (on a different RelationalModel) is the reverse
		 * relation of the current one.
		 * @param {Backbone.Relation} relation
		 * @return {Boolean}
		 */
		_isReverseRelation: function( relation ) {
			return relation.instance instanceof this.relatedModel && this.reverseRelation.key === relation.key &&
				this.key === relation.reverseRelation.key;
		},

		/**
		 * Get the reverse relations (pointing back to 'this.key' on 'this.instance') for the currently related model(s).
		 * @param {Backbone.RelationalModel} [model] Get the reverse relations for a specific model.
		 *    If not specified, 'this.related' is used.
		 * @return {Backbone.Relation[]}
		 */
		getReverseRelations: function( model ) {
			var reverseRelations = [];
			// Iterate over 'model', 'this.related.models' (if this.related is a Backbone.Collection), or wrap 'this.related' in an array.
			var models = !_.isUndefined( model ) ? [ model ] : this.related && ( this.related.models || [ this.related ] );
			_.each( models || [], function( related ) {
				_.each( related.getRelations() || [], function( relation ) {
						if ( this._isReverseRelation( relation ) ) {
							reverseRelations.push( relation );
						}
					}, this );
			}, this );
			
			return reverseRelations;
		},

		/**
		 * When `this.instance` is destroyed, cleanup our relations.
		 * Get reverse relation, call removeRelated on each.
		 */
		destroy: function() {
			this.stopListening();

			if ( this instanceof Backbone.HasOne ) {
				this.setRelated( null );
			}
			else if ( this instanceof Backbone.HasMany ) {
				this.setRelated( this._prepareCollection() );
			}
			
			_.each( this.getReverseRelations(), function( relation ) {
				relation.removeRelated( this.instance );
			}, this );
		}
	});

	Backbone.HasOne = Backbone.Relation.extend({
		options: {
			reverseRelation: { type: 'HasMany' }
		},

		initialize: function( opts ) {
			this.listenTo( this.instance, 'relational:change:' + this.key, this.onChange );

			var related = this.findRelated( opts );
			this.setRelated( related );

			// Notify new 'related' object of the new relation.
			_.each( this.getReverseRelations(), function( relation ) {
				relation.addRelated( this.instance, opts );
			}, this );
		},

		/**
		 * Find related Models.
		 * @param {Object} [options]
		 * @return {Backbone.Model}
		 */
		findRelated: function( options ) {
			var related = null;

			options = _.defaults( { parse: this.options.parse }, options );

			if ( this.keyContents instanceof this.relatedModel ) {
				related = this.keyContents;
			}
			else if ( this.keyContents || this.keyContents === 0 ) { // since 0 can be a valid `id` as well
				var opts = _.defaults( { create: this.options.createModels }, options );
				related = this.relatedModel.findOrCreate( this.keyContents, opts );
			}

			// Nullify `keyId` if we have a related model; in case it was already part of the relation
			if ( this.related ) {
				this.keyId = null;
			}

			return related;
		},

		/**
		 * Normalize and reduce `keyContents` to an `id`, for easier comparison
		 * @param {String|Number|Backbone.Model} keyContents
		 */
		setKeyContents: function( keyContents ) {
			this.keyContents = keyContents;
			this.keyId = Backbone.Relational.store.resolveIdForItem( this.relatedModel, this.keyContents );
		},

		/**
		 * Event handler for `change:<key>`.
		 * If the key is changed, notify old & new reverse relations and initialize the new relation.
		 */
		onChange: function( model, attr, options ) {
			// Don't accept recursive calls to onChange (like onChange->findRelated->findOrCreate->initializeRelations->addRelated->onChange)
			if ( this.isLocked() ) {
				return;
			}
			this.acquire();
			options = options ? _.clone( options ) : {};
			
			// 'options.__related' is set by 'addRelated'/'removeRelated'. If it is set, the change
			// is the result of a call from a relation. If it's not, the change is the result of 
			// a 'set' call on this.instance.
			var changed = _.isUndefined( options.__related ),
				oldRelated = changed ? this.related : options.__related;
			
			if ( changed ) {
				this.setKeyContents( attr );
				var related = this.findRelated( options );
				this.setRelated( related );
			}
			
			// Notify old 'related' object of the terminated relation
			if ( oldRelated && this.related !== oldRelated ) {
				_.each( this.getReverseRelations( oldRelated ), function( relation ) {
					relation.removeRelated( this.instance, null, options );
				}, this );
			}

			// Notify new 'related' object of the new relation. Note we do re-apply even if this.related is oldRelated;
			// that can be necessary for bi-directional relations if 'this.instance' was created after 'this.related'.
			// In that case, 'this.instance' will already know 'this.related', but the reverse might not exist yet.
			_.each( this.getReverseRelations(), function( relation ) {
				relation.addRelated( this.instance, options );
			}, this );
			
			// Fire the 'change:<key>' event if 'related' was updated
			if ( !options.silent && this.related !== oldRelated ) {
				var dit = this;
				this.changed = true;
				Backbone.Relational.eventQueue.add( function() {
					dit.instance.trigger( 'change:' + dit.key, dit.instance, dit.related, options, true );
					dit.changed = false;
				});
			}
			this.release();
		},

		/**
		 * If a new 'this.relatedModel' appears in the 'store', try to match it to the last set 'keyContents'
		 */
		tryAddRelated: function( model, coll, options ) {
			if ( ( this.keyId || this.keyId === 0 ) && model.id === this.keyId ) { // since 0 can be a valid `id` as well
				this.addRelated( model, options );
				this.keyId = null;
			}
		},

		addRelated: function( model, options ) {
			// Allow 'model' to set up its relations before proceeding.
			// (which can result in a call to 'addRelated' from a relation of 'model')
			var dit = this;
			model.queue( function() {
				if ( model !== dit.related ) {
					var oldRelated = dit.related || null;
					dit.setRelated( model );
					dit.onChange( dit.instance, model, _.defaults( { __related: oldRelated }, options ) );
				}
			});
		},

		removeRelated: function( model, coll, options ) {
			if ( !this.related ) {
				return;
			}
			
			if ( model === this.related ) {
				var oldRelated = this.related || null;
				this.setRelated( null );
				this.onChange( this.instance, model, _.defaults( { __related: oldRelated }, options ) );
			}
		}
	});

	Backbone.HasMany = Backbone.Relation.extend({
		collectionType: null,

		options: {
			reverseRelation: { type: 'HasOne' },
			collectionType: Backbone.Collection,
			collectionKey: true,
			collectionOptions: {}
		},

		initialize: function( opts ) {
			this.listenTo( this.instance, 'relational:change:' + this.key, this.onChange );
			
			// Handle a custom 'collectionType'
			this.collectionType = this.options.collectionType;
			if ( _.isString( this.collectionType ) ) {
				this.collectionType = Backbone.Relational.store.getObjectByName( this.collectionType );
			}
			if ( this.collectionType !== Backbone.Collection && !( this.collectionType.prototype instanceof Backbone.Collection ) ) {
				throw new Error( '`collectionType` must inherit from Backbone.Collection' );
			}

			var related = this.findRelated( opts );
			this.setRelated( related );
		},

		/**
		 * Bind events and setup collectionKeys for a collection that is to be used as the backing store for a HasMany.
		 * If no 'collection' is supplied, a new collection will be created of the specified 'collectionType' option.
		 * @param {Backbone.Collection} [collection]
		 * @return {Backbone.Collection}
		 */
		_prepareCollection: function( collection ) {
			if ( this.related ) {
				this.stopListening( this.related );
			}

			if ( !collection || !( collection instanceof Backbone.Collection ) ) {
				var options = _.isFunction( this.options.collectionOptions ) ?
					this.options.collectionOptions( this.instance ) : this.options.collectionOptions;

				collection = new this.collectionType( null, options );
			}

			collection.model = this.relatedModel;
			
			if ( this.options.collectionKey ) {
				var key = this.options.collectionKey === true ? this.options.reverseRelation.key : this.options.collectionKey;
				
				if ( collection[ key ] && collection[ key ] !== this.instance ) {
					if ( Backbone.Relational.showWarnings && typeof console !== 'undefined' ) {
						console.warn( 'Relation=%o; collectionKey=%s already exists on collection=%o', this, key, this.options.collectionKey );
					}
				}
				else if ( key ) {
					collection[ key ] = this.instance;
				}
			}

			this.listenTo( collection, 'relational:add', this.handleAddition )
				.listenTo( collection, 'relational:remove', this.handleRemoval )
				.listenTo( collection, 'relational:reset', this.handleReset );
			
			return collection;
		},

		/**
		 * Find related Models.
		 * @param {Object} [options]
		 * @return {Backbone.Collection}
		 */
		findRelated: function( options ) {
			var related = null;

			options = _.defaults( { parse: this.options.parse }, options );

			// Replace 'this.related' by 'this.keyContents' if it is a Backbone.Collection
			if ( this.keyContents instanceof Backbone.Collection ) {
				this._prepareCollection( this.keyContents );
				related = this.keyContents;
			}
			// Otherwise, 'this.keyContents' should be an array of related object ids.
			// Re-use the current 'this.related' if it is a Backbone.Collection; otherwise, create a new collection.
			else {
				var toAdd = [];

				_.each( this.keyContents, function( attributes ) {
					if ( attributes instanceof this.relatedModel ) {
						var model = attributes;
					}
					else {
						// If `merge` is true, update models here, instead of during update.
						model = this.relatedModel.findOrCreate( attributes,
							_.extend( { merge: true }, options, { create: this.options.createModels } )
						);
					}

					model && toAdd.push( model );
				}, this );

				if ( this.related instanceof Backbone.Collection ) {
					related = this.related;
				}
				else {
					related = this._prepareCollection();
				}

				// By now, both `merge` and `parse` will already have been executed for models if they were specified.
				// Disable them to prevent additional calls.
				related.set( toAdd, _.defaults( { merge: false, parse: false }, options ) );
			}

			// Remove entries from `keyIds` that were already part of the relation (and are thus 'unchanged')
			this.keyIds = _.difference( this.keyIds, _.pluck( related.models, 'id' ) );

			return related;
		},

		/**
		 * Normalize and reduce `keyContents` to a list of `ids`, for easier comparison
		 * @param {String|Number|String[]|Number[]|Backbone.Collection} keyContents
		 */
		setKeyContents: function( keyContents ) {
			this.keyContents = keyContents instanceof Backbone.Collection ? keyContents : null;
			this.keyIds = [];

			if ( !this.keyContents && ( keyContents || keyContents === 0 ) ) { // since 0 can be a valid `id` as well
				// Handle cases the an API/user supplies just an Object/id instead of an Array
				this.keyContents = _.isArray( keyContents ) ? keyContents : [ keyContents ];

				_.each( this.keyContents, function( item ) {
					var itemId = Backbone.Relational.store.resolveIdForItem( this.relatedModel, item );
					if ( itemId || itemId === 0 ) {
						this.keyIds.push( itemId );
					}
				}, this );
			}
		},

		/**
		 * Event handler for `change:<key>`.
		 * If the contents of the key are changed, notify old & new reverse relations and initialize the new relation.
		 */
		onChange: function( model, attr, options ) {
			options = options ? _.clone( options ) : {};
			this.setKeyContents( attr );
			this.changed = false;

			var related = this.findRelated( options );
			this.setRelated( related );

			if ( !options.silent ) {
				var dit = this;
				Backbone.Relational.eventQueue.add( function() {
					// The `changed` flag can be set in `handleAddition` or `handleRemoval`
					if ( dit.changed ) {
						dit.instance.trigger( 'change:' + dit.key, dit.instance, dit.related, options, true );
						dit.changed = false;
					}
				});
			}
		},

		/**
		 * When a model is added to a 'HasMany', trigger 'add' on 'this.instance' and notify reverse relations.
		 * (should be 'HasOne', must set 'this.instance' as their related).
		*/
		handleAddition: function( model, coll, options ) {
			//console.debug('handleAddition called; args=%o', arguments);
			options = options ? _.clone( options ) : {};
			this.changed = true;
			
			_.each( this.getReverseRelations( model ), function( relation ) {
				relation.addRelated( this.instance, options );
			}, this );

			// Only trigger 'add' once the newly added model is initialized (so, has its relations set up)
			var dit = this;
			!options.silent && Backbone.Relational.eventQueue.add( function() {
				dit.instance.trigger( 'add:' + dit.key, model, dit.related, options );
			});
		},

		/**
		 * When a model is removed from a 'HasMany', trigger 'remove' on 'this.instance' and notify reverse relations.
		 * (should be 'HasOne', which should be nullified)
		 */
		handleRemoval: function( model, coll, options ) {
			//console.debug('handleRemoval called; args=%o', arguments);
			options = options ? _.clone( options ) : {};
			this.changed = true;
			
			_.each( this.getReverseRelations( model ), function( relation ) {
				relation.removeRelated( this.instance, null, options );
			}, this );
			
			var dit = this;
			!options.silent && Backbone.Relational.eventQueue.add( function() {
				 dit.instance.trigger( 'remove:' + dit.key, model, dit.related, options );
			});
		},

		handleReset: function( coll, options ) {
			var dit = this;
			options = options ? _.clone( options ) : {};
			!options.silent && Backbone.Relational.eventQueue.add( function() {
				dit.instance.trigger( 'reset:' + dit.key, dit.related, options );
			});
		},

		tryAddRelated: function( model, coll, options ) {
			var item = _.contains( this.keyIds, model.id );

			if ( item ) {
				this.addRelated( model, options );
				this.keyIds = _.without( this.keyIds, model.id );
			}
		},

		addRelated: function( model, options ) {
			// Allow 'model' to set up its relations before proceeding.
			// (which can result in a call to 'addRelated' from a relation of 'model')
			var dit = this;
			model.queue( function() {
				if ( dit.related && !dit.related.get( model ) ) {
					dit.related.add( model, _.defaults( { parse: false }, options ) );
				}
			});
		},

		removeRelated: function( model, coll, options ) {
			if ( this.related.get( model ) ) {
				this.related.remove( model, options );
			}
		}
	});

	/**
	 * A type of Backbone.Model that also maintains relations to other models and collections.
	 * New events when compared to the original:
	 *  - 'add:<key>' (model, related collection, options)
	 *  - 'remove:<key>' (model, related collection, options)
	 *  - 'change:<key>' (model, related model or collection, options)
	 */
	Backbone.RelationalModel = Backbone.Model.extend({
		relations: null, // Relation descriptions on the prototype
		_relations: null, // Relation instances
		_isInitialized: false,
		_deferProcessing: false,
		_queue: null,

		subModelTypeAttribute: 'type',
		subModelTypes: null,

		constructor: function( attributes, options ) {
			// Nasty hack, for cases like 'model.get( <HasMany key> ).add( item )'.
			// Defer 'processQueue', so that when 'Relation.createModels' is used we trigger 'HasMany'
			// collection events only after the model is really fully set up.
			// Example: event for "p.on( 'add:jobs' )" -> "p.get('jobs').add( { company: c.id, person: p.id } )".
			if ( options && options.collection ) {
				var dit = this,
					collection = this.collection =  options.collection;

				// Prevent `collection` from cascading down to nested models; they shouldn't go into this `if` clause.
				delete options.collection;

				this._deferProcessing = true;

				var processQueue = function( model ) {
					if ( model === dit ) {
						dit._deferProcessing = false;
						dit.processQueue();
						collection.off( 'relational:add', processQueue );
					}
				};
				collection.on( 'relational:add', processQueue );

				// So we do process the queue eventually, regardless of whether this model actually gets added to 'options.collection'.
				_.defer( function() {
					processQueue( dit );
				});
			}

			Backbone.Relational.store.processOrphanRelations();
			
			this._queue = new Backbone.BlockingQueue();
			this._queue.block();
			Backbone.Relational.eventQueue.block();

			try {
				Backbone.Model.apply( this, arguments );
			}
			finally {
				// Try to run the global queue holding external events
				Backbone.Relational.eventQueue.unblock();
			}
		},

		/**
		 * Override 'trigger' to queue 'change' and 'change:*' events
		 */
		trigger: function( eventName ) {
			if ( eventName.length > 5 && eventName.indexOf( 'change' ) === 0 ) {
				var dit = this,
					args = arguments;

				Backbone.Relational.eventQueue.add( function() {
					if ( !dit._isInitialized ) {
						return;
					}

					// Determine if the `change` event is still valid, now that all relations are populated
					var changed = true;
					if ( eventName === 'change' ) {
						changed = dit.hasChanged();
					}
					else {
						var attr = eventName.slice( 7 ),
							rel = dit.getRelation( attr );

						if ( rel ) {
							// If `attr` is a relation, `change:attr` get triggered from `Relation.onChange`.
							// These take precedence over `change:attr` events triggered by `Model.set`.
							// The relation set a fourth attribute to `true`. If this attribute is present,
							// continue triggering this event; otherwise, it's from `Model.set` and should be stopped.
							changed = ( args[ 4 ] === true );

							// If this event was triggered by a relation, set the right value in `this.changed`
							// (a Collection or Model instead of raw data).
							if ( changed ) {
								dit.changed[ attr ] = args[ 2 ];
							}
							// Otherwise, this event is from `Model.set`. If the relation doesn't report a change,
							// remove attr from `dit.changed` so `hasChanged` doesn't take it into account.
							else if ( !rel.changed ) {
								delete dit.changed[ attr ];
							}
						}
					}

					changed && Backbone.Model.prototype.trigger.apply( dit, args );
				});
			}
			else {
				Backbone.Model.prototype.trigger.apply( this, arguments );
			}

			return this;
		},

		/**
		 * Initialize Relations present in this.relations; determine the type (HasOne/HasMany), then creates a new instance.
		 * Invoked in the first call so 'set' (which is made from the Backbone.Model constructor).
		 */
		initializeRelations: function( options ) {
			this.acquire(); // Setting up relations often also involve calls to 'set', and we only want to enter this function once
			this._relations = {};

			_.each( this.relations || [], function( rel ) {
				Backbone.Relational.store.initializeRelation( this, rel, options );
			}, this );

			this._isInitialized = true;
			this.release();
			this.processQueue();
		},

		/**
		 * When new values are set, notify this model's relations (also if options.silent is set).
		 * (Relation.setRelated locks this model before calling 'set' on it to prevent loops)
		 */
		updateRelations: function( options ) {
			if ( this._isInitialized && !this.isLocked() ) {
				_.each( this._relations, function( rel ) {
					// Update from data in `rel.keySource` if set, or `rel.key` otherwise
					var val = this.attributes[ rel.keySource ] || this.attributes[ rel.key ];
					if ( rel.related !== val ) {
						this.trigger( 'relational:change:' + rel.key, this, val, options || {} );
					}
				}, this );
			}
		},

		/**
		 * Either add to the queue (if we're not initialized yet), or execute right away.
		 */
		queue: function( func ) {
			this._queue.add( func );
		},

		/**
		 * Process _queue
		 */
		processQueue: function() {
			if ( this._isInitialized && !this._deferProcessing && this._queue.isBlocked() ) {
				this._queue.unblock();
			}
		},

		/**
		 * Get a specific relation.
		 * @param key {string} The relation key to look for.
		 * @return {Backbone.Relation} An instance of 'Backbone.Relation', if a relation was found for 'key', or null.
		 */
		getRelation: function( key ) {
			return this._relations[ key ];
		},

		/**
		 * Get all of the created relations.
		 * @return {Backbone.Relation[]}
		 */
		getRelations: function() {
			return _.values( this._relations );
		},

		/**
		 * Retrieve related objects.
		 * @param key {string} The relation key to fetch models for.
		 * @param [options] {Object} Options for 'Backbone.Model.fetch' and 'Backbone.sync'.
		 * @param [refresh=false] {boolean} Fetch existing models from the server as well (in order to update them).
		 * @return {jQuery.when[]} An array of request objects
		 */
		fetchRelated: function( key, options, refresh ) {
			// Set default `options` for fetch
			options = _.extend( { update: true, remove: false }, options );

			var setUrl,
				requests = [],
				rel = this.getRelation( key ),
				idsToFetch = rel && ( rel.keyIds || ( ( rel.keyId || rel.keyId === 0 ) ? [ rel.keyId ] : [] ) );

			// On `refresh`, add the ids for current models in the relation to `idsToFetch`
			if ( refresh ) {
				var models = rel.related instanceof Backbone.Collection ? rel.related.models : [ rel.related ];
				_.each( models, function( model ) {
					if ( model.id || model.id === 0 ) {
						idsToFetch.push( model.id );
					}
				});
			}

			if ( idsToFetch && idsToFetch.length ) {
				// Find (or create) a model for each one that is to be fetched
				var created = [],
					models = _.map( idsToFetch, function( id ) {
						var model = Backbone.Relational.store.find( rel.relatedModel, id );
						
						if ( !model ) {
							var attrs = {};
							attrs[ rel.relatedModel.prototype.idAttribute ] = id;
							model = rel.relatedModel.findOrCreate( attrs, options );
							created.push( model );
						}

						return model;
					}, this );
				
				// Try if the 'collection' can provide a url to fetch a set of models in one request.
				if ( rel.related instanceof Backbone.Collection && _.isFunction( rel.related.url ) ) {
					setUrl = rel.related.url( models );
				}

				// An assumption is that when 'Backbone.Collection.url' is a function, it can handle building of set urls.
				// To make sure it can, test if the url we got by supplying a list of models to fetch is different from
				// the one supplied for the default fetch action (without args to 'url').
				if ( setUrl && setUrl !== rel.related.url() ) {
					var opts = _.defaults(
						{
							error: function() {
								var args = arguments;
								_.each( created, function( model ) {
									model.trigger( 'destroy', model, model.collection, options );
									options.error && options.error.apply( model, args );
								});
							},
							url: setUrl
						},
						options
					);

					requests = [ rel.related.fetch( opts ) ];
				}
				else {
					requests = _.map( models, function( model ) {
						var opts = _.defaults(
							{
								error: function() {
									if ( _.contains( created, model ) ) {
										model.trigger( 'destroy', model, model.collection, options );
										options.error && options.error.apply( model, arguments );
									}
								}
							},
							options
						);
						return model.fetch( opts );
					}, this );
				}
			}
			
			return requests;
		},

		get: function( attr ) {
			var originalResult = Backbone.Model.prototype.get.call( this, attr );

			// Use `originalResult` get if dotNotation not enabled or not required because no dot is in `attr`
			if ( !this.dotNotation || attr.indexOf( '.' ) === -1 ) {
				return originalResult;
			}

			// Go through all splits and return the final result
			var splits = attr.split( '.' );
			var result = _.reduce(splits, function( model, split ) {
				if ( !( model instanceof Backbone.Model ) ) {
					throw new Error( 'Attribute must be an instanceof Backbone.Model. Is: ' + model + ', currentSplit: ' + split );
				}

				return Backbone.Model.prototype.get.call( model, split );
			}, this );

			if ( originalResult !== undefined && result !== undefined ) {
				throw new Error( "Ambiguous result for '" + attr + "'. direct result: " + originalResult + ", dotNotation: " + result );
			}

			return originalResult || result;
		},

		set: function( key, value, options ) {
			Backbone.Relational.eventQueue.block();

			// Duplicate backbone's behavior to allow separate key/value parameters, instead of a single 'attributes' object
			var attributes;
			if ( _.isObject( key ) || key == null ) {
				attributes = key;
				options = value;
			}
			else {
				attributes = {};
				attributes[ key ] = value;
			}

			try {
				var id = this.id,
					newId = attributes && this.idAttribute in attributes && attributes[ this.idAttribute ];

				// Check if we're not setting a duplicate id before actually calling `set`.
				Backbone.Relational.store.checkId( this, newId );

				var result = Backbone.Model.prototype.set.apply( this, arguments );

				// Ideal place to set up relations, if this is the first time we're here for this model
				if ( !this._isInitialized && !this.isLocked() ) {
					this.constructor.initializeModelHierarchy();
					Backbone.Relational.store.register( this );
					this.initializeRelations( options );
				}
				// The store should know about an `id` update asap
				else if ( newId && newId !== id ) {
					Backbone.Relational.store.update( this );
				}

				if ( attributes ) {
					this.updateRelations( options );
				}
			}
			finally {
				// Try to run the global queue holding external events
				Backbone.Relational.eventQueue.unblock();
			}
			
			return result;
		},

		unset: function( attribute, options ) {
			Backbone.Relational.eventQueue.block();

			var result = Backbone.Model.prototype.unset.apply( this, arguments );
			this.updateRelations( options );

			// Try to run the global queue holding external events
			Backbone.Relational.eventQueue.unblock();

			return result;
		},

		clear: function( options ) {
			Backbone.Relational.eventQueue.block();
			
			var result = Backbone.Model.prototype.clear.apply( this, arguments );
			this.updateRelations( options );

			// Try to run the global queue holding external events
			Backbone.Relational.eventQueue.unblock();

			return result;
		},

		clone: function() {
			var attributes = _.clone( this.attributes );
			if ( !_.isUndefined( attributes[ this.idAttribute ] ) ) {
				attributes[ this.idAttribute ] = null;
			}

			_.each( this.getRelations(), function( rel ) {
				delete attributes[ rel.key ];
			});

			return new this.constructor( attributes );
		},

		/**
		 * Convert relations to JSON, omits them when required
		 */
		toJSON: function( options ) {
			// If this Model has already been fully serialized in this branch once, return to avoid loops
			if ( this.isLocked() ) {
				return this.id;
			}

			this.acquire();
			var json = Backbone.Model.prototype.toJSON.call( this, options );

			if ( this.constructor._superModel && !( this.constructor._subModelTypeAttribute in json ) ) {
				json[ this.constructor._subModelTypeAttribute ] = this.constructor._subModelTypeValue;
			}

			_.each( this._relations, function( rel ) {
				var related = json[ rel.key ],
					includeInJSON = rel.options.includeInJSON,
					value = null;

				if ( includeInJSON === true ) {
					if ( related && _.isFunction( related.toJSON ) ) {
						value = related.toJSON( options );
					}
				}
				else if ( _.isString( includeInJSON ) ) {
					if ( related instanceof Backbone.Collection ) {
						value = related.pluck( includeInJSON );
					}
					else if ( related instanceof Backbone.Model ) {
						value = related.get( includeInJSON );
					}

					// Add ids for 'unfound' models if includeInJSON is equal to (only) the relatedModel's `idAttribute`
					if ( includeInJSON === rel.relatedModel.prototype.idAttribute ) {
						if ( rel instanceof Backbone.HasMany ) {
							value = value.concat( rel.keyIds );
						}
						else if  ( rel instanceof Backbone.HasOne ) {
							value = value || rel.keyId;
						}
					}
				}
				else if ( _.isArray( includeInJSON ) ) {
					if ( related instanceof Backbone.Collection ) {
						value = [];
						related.each( function( model ) {
							var curJson = {};
							_.each( includeInJSON, function( key ) {
								curJson[ key ] = model.get( key );
							});
							value.push( curJson );
						});
					}
					else if ( related instanceof Backbone.Model ) {
						value = {};
						_.each( includeInJSON, function( key ) {
							value[ key ] = related.get( key );
						});
					}
				}
				else {
					delete json[ rel.key ];
				}

				if ( includeInJSON ) {
					json[ rel.keyDestination ] = value;
				}

				if ( rel.keyDestination !== rel.key ) {
					delete json[ rel.key ];
				}
			});
			
			this.release();
			return json;
		}
	},
	{
		/**
		 *
		 * @param superModel
		 * @returns {Backbone.RelationalModel.constructor}
		 */
		setup: function( superModel ) {
			// We don't want to share a relations array with a parent, as this will cause problems with
			// reverse relations.
			this.prototype.relations = ( this.prototype.relations || [] ).slice( 0 );

			this._subModels = {};
			this._superModel = null;

			// If this model has 'subModelTypes' itself, remember them in the store
			if ( this.prototype.hasOwnProperty( 'subModelTypes' ) ) {
				Backbone.Relational.store.addSubModels( this.prototype.subModelTypes, this );
			}
			// The 'subModelTypes' property should not be inherited, so reset it.
			else {
				this.prototype.subModelTypes = null;
			}

			// Initialize all reverseRelations that belong to this new model.
			_.each( this.prototype.relations || [], function( rel ) {
				if ( !rel.model ) {
					rel.model = this;
				}
				
				if ( rel.reverseRelation && rel.model === this ) {
					var preInitialize = true;
					if ( _.isString( rel.relatedModel ) ) {
						/**
						 * The related model might not be defined for two reasons
						 *  1. it is related to itself
						 *  2. it never gets defined, e.g. a typo
						 *  3. the model hasn't been defined yet, but will be later
						 * In neither of these cases do we need to pre-initialize reverse relations.
						 * However, for 3. (which is, to us, indistinguishable from 2.), we do need to attempt
						 * setting up this relation again later, in case the related model is defined later.
						 */
						var relatedModel = Backbone.Relational.store.getObjectByName( rel.relatedModel );
						preInitialize = relatedModel && ( relatedModel.prototype instanceof Backbone.RelationalModel );
					}

					if ( preInitialize ) {
						Backbone.Relational.store.initializeRelation( null, rel );
					}
					else if ( _.isString( rel.relatedModel ) ) {
						Backbone.Relational.store.addOrphanRelation( rel );
					}
				}
			}, this );
			
			return this;
		},

		/**
		 * Create a 'Backbone.Model' instance based on 'attributes'.
		 * @param {Object} attributes
		 * @param {Object} [options]
		 * @return {Backbone.Model}
		 */
		build: function( attributes, options ) {
			var model = this;

			// 'build' is a possible entrypoint; it's possible no model hierarchy has been determined yet.
			this.initializeModelHierarchy();

			// Determine what type of (sub)model should be built if applicable.
			// Lookup the proper subModelType in 'this._subModels'.
			if ( this._subModels && this.prototype.subModelTypeAttribute in attributes ) {
				var subModelTypeAttribute = attributes[ this.prototype.subModelTypeAttribute ];
				var subModelType = this._subModels[ subModelTypeAttribute ];
				if ( subModelType ) {
					model = subModelType;
				}
			}
			
			return new model( attributes, options );
		},

		/**
		 *
		 */
		initializeModelHierarchy: function() {
			// If we're here for the first time, try to determine if this modelType has a 'superModel'.
			if ( _.isUndefined( this._superModel ) || _.isNull( this._superModel ) ) {
				Backbone.Relational.store.setupSuperModel( this );

				// If a superModel has been found, copy relations from the _superModel if they haven't been
				// inherited automatically (due to a redefinition of 'relations').
				// Otherwise, make sure we don't get here again for this type by making '_superModel' false so we fail
				// the isUndefined/isNull check next time.
				if ( this._superModel && this._superModel.prototype.relations ) {
					// Find relations that exist on the `_superModel`, but not yet on this model.
					var inheritedRelations = _.select( this._superModel.prototype.relations || [], function( superRel ) {
						return !_.any( this.prototype.relations || [], function( rel ) {
							return superRel.relatedModel === rel.relatedModel && superRel.key === rel.key;
						}, this );
					}, this );

					this.prototype.relations = inheritedRelations.concat( this.prototype.relations );
				}
				else {
					this._superModel = false;
				}
			}

			// If we came here through 'build' for a model that has 'subModelTypes', and not all of them have been resolved yet, try to resolve each.
			if ( this.prototype.subModelTypes && _.keys( this.prototype.subModelTypes ).length !== _.keys( this._subModels ).length ) {
				_.each( this.prototype.subModelTypes || [], function( subModelTypeName ) {
					var subModelType = Backbone.Relational.store.getObjectByName( subModelTypeName );
					subModelType && subModelType.initializeModelHierarchy();
				});
			}
		},

		/**
		 * Find an instance of `this` type in 'Backbone.Relational.store'.
		 * - If `attributes` is a string or a number, `findOrCreate` will just query the `store` and return a model if found.
		 * - If `attributes` is an object and is found in the store, the model will be updated with `attributes` unless `options.update` is `false`.
		 *   Otherwise, a new model is created with `attributes` (unless `options.create` is explicitly set to `false`).
		 * @param {Object|String|Number} attributes Either a model's id, or the attributes used to create or update a model.
		 * @param {Object} [options]
		 * @param {Boolean} [options.create=true]
		 * @param {Boolean} [options.merge=true]
		 * @param {Boolean} [options.parse=false]
		 * @return {Backbone.RelationalModel}
		 */
		findOrCreate: function( attributes, options ) {
			options || ( options = {} );
			var parsedAttributes = ( _.isObject( attributes ) && options.parse && this.prototype.parse ) ?
				this.prototype.parse( attributes ) : attributes;

			// Try to find an instance of 'this' model type in the store
			var model = Backbone.Relational.store.find( this, parsedAttributes );

			// If we found an instance, update it with the data in 'item' (unless 'options.merge' is false).
			// If not, create an instance (unless 'options.create' is false).
			if ( _.isObject( attributes ) ) {
				if ( model && options.merge !== false ) {
					// Make sure `options.collection` doesn't cascade to nested models
					delete options.collection;

					model.set( parsedAttributes, options );
				}
				else if ( !model && options.create !== false ) {
					model = this.build( attributes, options );
				}
			}

			return model;
		}
	});
	_.extend( Backbone.RelationalModel.prototype, Backbone.Semaphore );

	/**
	 * Override Backbone.Collection._prepareModel, so objects will be built using the correct type
	 * if the collection.model has subModels.
	 * Attempts to find a model for `attrs` in Backbone.store through `findOrCreate`
	 * (which sets the new properties on it if found), or instantiates a new model.
	 */
	Backbone.Collection.prototype.__prepareModel = Backbone.Collection.prototype._prepareModel;
	Backbone.Collection.prototype._prepareModel = function ( attrs, options ) {
		var model;
		
		if ( attrs instanceof Backbone.Model ) {
			if ( !attrs.collection ) {
				attrs.collection = this;
			}
			model = attrs;
		}
		else {
			options || ( options = {} );
			options.collection = this;
			
			if ( typeof this.model.findOrCreate !== 'undefined' ) {
				model = this.model.findOrCreate( attrs, options );
			}
			else {
				model = new this.model( attrs, options );
			}
			
			if ( model && model.isNew() && !model._validate( attrs, options ) ) {
				this.trigger( 'invalid', this, attrs, options );
				model = false;
			}
		}
		
		return model;
	};


	/**
	 * Override Backbone.Collection.set, so we'll create objects from attributes where required,
	 * and update the existing models. Also, trigger 'relational:add'.
	 */
	var set = Backbone.Collection.prototype.__set = Backbone.Collection.prototype.set;
	Backbone.Collection.prototype.set = function( models, options ) {
		// Short-circuit if this Collection doesn't hold RelationalModels
		if ( !( this.model.prototype instanceof Backbone.RelationalModel ) ) {
			return set.apply( this, arguments );
		}

		if ( options && options.parse ) {
			models = this.parse( models, options );
		}

		if ( !_.isArray( models ) ) {
			models = models ? [ models ] : [];
		}

		var newModels = [],
			toAdd = [];

		//console.debug( 'calling add on coll=%o; model=%o, options=%o', this, models, options );
		_.each( models, function( model ) {
			if ( !( model instanceof Backbone.Model ) ) {
				model = Backbone.Collection.prototype._prepareModel.call( this, model, options );
			}

			if ( model ) {
				toAdd.push( model );

				if ( !( this.get( model ) || this.get( model.cid ) ) ) {
					newModels.push( model );
				}
				// If we arrive in `add` while performing a `set` (after a create, so the model gains an `id`),
				// we may get here before `_onModelEvent` has had the chance to update `_byId`.
				else if ( model.id != null ) {
					this._byId[ model.id ] = model;
				}
			}
		}, this );

		// Add 'models' in a single batch, so the original add will only be called once (and thus 'sort', etc).
		// If `parse` was specified, the collection and contained models have been parsed now.
		set.call( this, toAdd, _.defaults( { parse: false }, options ) );

		_.each( newModels, function( model ) {
			// Fire a `relational:add` event for any model in `newModels` that has actually been added to the collection.
			if ( this.get( model ) || this.get( model.cid ) ) {
				this.trigger( 'relational:add', model, this, options );
			}
		}, this );
		
		return this;
	};

	/**
	 * Override 'Backbone.Collection.remove' to trigger 'relational:remove'.
	 */
	var remove = Backbone.Collection.prototype.__remove = Backbone.Collection.prototype.remove;
	Backbone.Collection.prototype.remove = function( models, options ) {
		// Short-circuit if this Collection doesn't hold RelationalModels
		if ( !( this.model.prototype instanceof Backbone.RelationalModel ) ) {
			return remove.apply( this, arguments );
		}

		models = _.isArray( models ) ? models.slice() : [ models ];
		options || ( options = {} );

		var toRemove = [];

		//console.debug('calling remove on coll=%o; models=%o, options=%o', this, models, options );
		_.each( models, function( model ) {
			model = this.get( model ) || this.get( model.cid );
			model && toRemove.push( model );
		}, this );

		if ( toRemove.length ) {
			remove.call( this, toRemove, options );

			_.each( toRemove, function( model ) {
				this.trigger('relational:remove', model, this, options);
			}, this );
		}
		
		return this;
	};

	/**
	 * Override 'Backbone.Collection.reset' to trigger 'relational:reset'.
	 */
	var reset = Backbone.Collection.prototype.__reset = Backbone.Collection.prototype.reset;
	Backbone.Collection.prototype.reset = function( models, options ) {
		options = _.extend( { merge: true }, options );
		reset.call( this, models, options );

		if ( this.model.prototype instanceof Backbone.RelationalModel ) {
			this.trigger( 'relational:reset', this, options );
		}

		return this;
	};

	/**
	 * Override 'Backbone.Collection.sort' to trigger 'relational:reset'.
	 */
	var sort = Backbone.Collection.prototype.__sort = Backbone.Collection.prototype.sort;
	Backbone.Collection.prototype.sort = function( options ) {
		sort.call( this, options );

		if ( this.model.prototype instanceof Backbone.RelationalModel ) {
			this.trigger( 'relational:reset', this, options );
		}

		return this;
	};

	/**
	 * Override 'Backbone.Collection.trigger' so 'add', 'remove' and 'reset' events are queued until relations
	 * are ready.
	 */
	var trigger = Backbone.Collection.prototype.__trigger = Backbone.Collection.prototype.trigger;
	Backbone.Collection.prototype.trigger = function( eventName ) {
		// Short-circuit if this Collection doesn't hold RelationalModels
		if ( !( this.model.prototype instanceof Backbone.RelationalModel ) ) {
			return trigger.apply( this, arguments );
		}

		if ( eventName === 'add' || eventName === 'remove' || eventName === 'reset' ) {
			var dit = this,
				args = arguments;
			
			if ( _.isObject( args[ 3 ] ) ) {
				args = _.toArray( args );
				// the fourth argument is the option object.
				// we need to clone it, as it could be modified while we wait on the eventQueue to be unblocked
				args[ 3 ] = _.clone( args[ 3 ] );
			}
			
			Backbone.Relational.eventQueue.add( function() {
				trigger.apply( dit, args );
			});
		}
		else {
			trigger.apply( this, arguments );
		}
		
		return this;
	};

	// Override .extend() to automatically call .setup()
	Backbone.RelationalModel.extend = function( protoProps, classProps ) {
		var child = Backbone.Model.extend.apply( this, arguments );
		
		child.setup( this );

		return child;
	};
        
})();

define("backbone-relational", ["backbone"], (function (global) {
    return function () {
        var ret, fn;
        return ret || global.Backbone;
    };
}(this)));

define('backbone-loader',[
  'backbone',
  'backbone-relational'
  ], function(Backbone) {
    return Backbone;
});
define('app/models/user',[
    'module',
    'backbone-loader',
], function(module, Backbone){
    var User = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        desiresLimit: module.config().desiresLimit,
        notifyRemoveDesire: function() {
            this.set('desires_count', this.get('desires_count')-1);
        },
        notifyCreateDesire: function() {
            this.set('desires_count', this.get('desires_count')+1);
        },
        canCreateDesire: function() {
            return this.get('desires_count') < this.desiresLimit;
        }
        
    });
    
    return User;
});

define('app/models/join',[
    'module',
    'backbone-loader',
    'app/models/user',
    'app/models/desire',
], function(module,Backbone, User, Desire){
     var Join = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        relations: [
            {
                type: Backbone.HasOne,
                key: 'user',
                relatedModel: User
            },
            {
                type: Backbone.HasOne,
                key: 'desire',
                relatedModel: Desire
            }
        ]
    });
    
    return Join;
});

define('app/collections/joins',[
    'backbone-loader',
    'app/models/join',
], function(Backbone, Join){
    var JoinCollection = Backbone.Collection.extend({
        model: Join
    });
    
    return JoinCollection;
});

define('app/models/desire',[
    'module',
    'backbone-loader',
    'app/models/game',
    'app/models/user',
    'app/models/join',
    'app/collections/joins',
], function(module, Backbone, Game, User, Join, JoinCollection) {

    var Desire = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        relations: [
            {
                type: Backbone.HasOne,
                key: 'game',
                relatedModel: Game
            },
            {
                type: Backbone.HasOne,
                key: 'owner',
                relatedModel: User
            },
            {
                type: Backbone.HasMany,
                key: 'joins',
                relatedModel: Join,
                collectionType: JoinCollection
            }
        ],
        addJoin: function(user) {
            var desire = this
            var join = new Join({user: user, desire: desire});
            join.save({}, {
                success:function(e){
                    desire.get('joins').add(join);
            }});
            
        },
        removeJoinById: function(id) {
            var joins = this.get('joins');
            var join = joins.get(id);
            joins.remove(join);
            join.destroy();
            
            if (!this.get('owner') && !joins.length) {
                this.get('game').removeDesire(this);
            }
            
        },
        removeJoin: function(user) {
            var joins = this.get('joins');
            var i = 0;
            while (i < joins.length) {
                var join = joins.at(i);
                if (join.get('user').id == user.id) {
                    join.destroy();
                    joins.remove(join);
                    //this.set('joins', joins);
                    break;
                }
                i++;
            }
            if (!this.get('owner') && !joins.length) {
                this.get('game').removeDesire(this);
            }
        },
        takeDesire: function(user) {
            this.set('owner', user);
            this.save();
            user.notifyCreateDesire();
        },
        leaveDesire: function(user) {
                var owner = this.get('owner');
                this.set('owner', null);
                owner.notifyRemoveDesire();
//            if (this.get('owner').id == user.id) {
//                this.set('owner', null);
//                user.notifyRemoveDesire();
//            } else {
//                this.removeJoin(user);
//            }
            if (!this.get('joins').length) {
                this.get('game').removeDesire();
            } else {
                this.save();
            }
        }
    });

    return Desire;
});

define('app/models/game',[
    'module',
    'backbone-loader',
    'app/models/desire',
], function(module, Backbone, Desire) {

    var Game = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        relations: [
            {
                type: Backbone.HasOne,
                key: 'desire',
                relatedModel: Desire,
                reverseRelation: {
			key: 'game',
			includeInJSON: 'id',
                        type: Backbone.HasOne,
		}
            }
        ],
        createDesire: function(user) {
            var desire = new Desire({owner: user, game: this});
            desire.save();
            this.set('desire', desire);
            user.notifyCreateDesire();
        },
        removeDesire: function() {
            var desire = this.get('desire');
            this.set('desire', false);
            desire.destroy();
        },
        takeDesire: function(user) {
            var desire = this.get('desire');
            return desire.takeDesire(user);
        },
        leaveDesire: function(user) {
            var desire = this.get('desire');
            return desire.leaveDesire(user);
        }
    });

    return Game;
});

define('app/collections/games',[
    'module',
    'backbone-loader',
    'app/models/game',
], function(module, Backbone, Game){
    var config = module.config();
    
    var GameCollection = Backbone.Collection.extend({
        url: config.url,
        model: Game
    });
    return GameCollection;
});

define('app/collections/desired-games',[
    'module',
    'backbone-loader',
    'app/models/game',
], function(module, Backbone, Game){
    
    var DesiredGameCollection = Backbone.Collection.extend({
        url: module.config().url,
        model: Game
    });
    return DesiredGameCollection;
});

define('app/views/game-count',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
], function($, _, Backbone){

    var GameCountView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#game-count').html()),
        render: function() {
            this.$el.html(this.template({count: this.model.length}));
            return this;
        },
    });
    
    return GameCountView;
});

define('app/collections/users',[
    'backbone-loader',
    'app/models/user',
], function(Backbone, User){
    var UserCollection = Backbone.Collection.extend({
        model: User
    });
    return UserCollection;
});

define('app/views/desire',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/collections/users',
], function($, _, Backbone, UserCollection){

    var DesireView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model.desire, 'change', this.render);
            this.listenTo(this.model.desire, 'add:joins', this.render);
            this.listenTo(this.model.desire, 'remove:joins', this.render);
            this.setElement($('#game-status-desire').html());
            this.render();
        },
        events: {
            "click .join-add": "addJoin",
            "click .join-remove": "removeJoin",
            "click .desire-take": "takeDesire",
            "click .desire-leave": "leaveDesire"
        },
        render: function() {
            var user = this.model.user;
            var game = this.model.game;
            var desire = this.model.game.get('desire');
            
            if (desire.get('owner')) {
                this.$el.html(_.template($('#game-status-desire-owner-player_main').html(), {user: user, owner: desire.get('owner')}));
            } else {
                if (user.canCreateDesire()) {
                    this.$el.html(_.template($('#game-status-desire-noowner-player_main').html(), {user: user, owner: desire.get('owner')}));
                } else {
                    this.$el.html(_.template($('#game-status-desire-noowner-player_main-limited').html(), {user: user, owner: desire.get('owner')}));
                }
            }

            var joins = desire.get('joins');
            var users = new UserCollection([desire.get('owner')]);
            
            for(var i=0; i<game.get('maxPlayers')-1; i++) {
                var join  = joins.at(i);
                if ( join) {
                    var guest = users.get(join.get('user')) != undefined;
                    users.push(join.get('user'));
                    this.$el.append(_.template($('#game-status-desire-player_joined').html(), {user: user, join: join, guest:guest}));
                } else {
                    this.$el.append(_.template($('#game-status-desire-player_nobody').html(), {user: user}));
                }
            }
    
            return this;
        },
        addJoin: function() {
            this.model.desire.addJoin(this.model.user);
            return false;
        },
        removeJoin: function(e) {
            var id = $(e.currentTarget).data('join-id');
            this.model.desire.removeJoinById(id);
            //this.model.desire.removeJoin(this.model.user);
            return false;
        },        
        takeDesire: function() {
            this.model.game.takeDesire(this.model.user);
            return false;
        },
        leaveDesire: function() {
            this.model.game.leaveDesire(this.model.user);
            return false;
        }
    });
    return DesireView;
});

define('app/views/game-status',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/desire',
], function($, _, Backbone, DesireView){

    var GameStatusView = Backbone.View.extend({
        
        initialize: function() {
            this.listenTo(this.model.game, 'change', this.render);
            this.listenTo(this.model.user, 'change', this.render);
            this.render()
        },
        events: {
            "click .desire-create": "createDesire",
        },
        render: function() {
            var desire = this.model.game.get('desire');
            if (!desire) {
                if (this.model.user.isLogged()) {
                    if (this.model.user.canCreateDesire()) {
                        this.template = _.template($('#game-status-user-nodesire').html()),
                        this.$el.html(this.template(this.model));
                        return this;
                    } else {
                        this.template = _.template($('#game-status-user-nodesire-limited').html()),
                        this.$el.html(this.template(this.model));
                        return this;
                    }
                } else {
                    this.template = _.template($('#game-status-nouser-nodesire').html()),
                    this.$el.html(this.template({}));
                    return this;
                }
            }
            
            var desireView = new DesireView({
                model: {
                    user: this.model.user,
                    game: this.model.game,
                    desire: desire
                }                    
            });
            this.$el.html(desireView.el);
        },
        createDesire: function() {
            this.model.game.createDesire(this.model.user);
            return false;
        }
    });
    return GameStatusView;
});

define('app/util/current-user',[
    'module',
    'app/models/user',
], function(module, User){
    var config = module.config();

    var CurrentUser = User.extend({
        url: config.url,
        isLogged: function() {
            return this.get('id');
        }
    });
    
    return new CurrentUser();
});

define('app/views/desired-game',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/game-status',
  'app/util/current-user',
], function($, _, Backbone, GameStatusView, currentUser){

    var DesiredGameView = Backbone.View.extend({
        tagName: 'div',
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },
        template: _.template($('#desired-game').html()),
        render: function() {
            this.setElement(this.template({game: this.model.toJSON()}));
            new GameStatusView({
                el: this.$el.find(".game-status"),
                model: {
                    game: this.model,
                    user: currentUser
                } 
            });
            return this;
        }
    });
    return DesiredGameView;
});

define('app/views/desired-game-list',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/desired-game',
  'masonry/masonry',
], function($, _, Backbone, DesiredGameView, Masonry){

    var DesiredGameListView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#desired-game-list').html()),
        render: function() {
            this.$el.html(this.template(this.model));
            this.model.forEach(this.renderGame);
            return this;
        },
        renderGame: function(game, index, games) {
            var desiredGameView = new DesiredGameView({
                model: game
            });
            var el = desiredGameView.render().el;
            this.$('div.desired-game-list').append(el);
            
            if (index==games.length-1) {
                var masonry = new Masonry(this.$('.masonry').get(0),{
                    itemSelector: '.item',
                    "gutter": 10
                });
                setInterval(function(){
                    masonry.layout();
                }, 20000)
            }
        },
        
    });
    return DesiredGameListView;
});

define('app/views/game',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/game-status',
  'app/util/current-user',
], function($, _, Backbone, GameStatusView, currentUser){

    var GameView = Backbone.View.extend({
        tagName: 'tr',
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },
        template: _.template($('#game').html()),
        render: function() {
            this.setElement(this.template({game: this.model.toJSON()}));
            new GameStatusView({
                el: this.$el.find(".game-status"),
                model: {
                    game: this.model,
                    user: currentUser
                }
            });
            return this;
        }
    });
    return GameView;
});

define('app/views/game-list',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/game',
], function($, _, Backbone, GameView){

    var GameListView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#game-list').html()),
        render: function() {
            this.$el.parents().find(".loading").hide();
            this.$el.html(this.template(this.model));
            this.model.forEach(this.renderGame);
            return this;
        },
        renderGame: function(game) {
            var gameView = new GameView({
                model: game
            });
            this.$('table.game-list').append(gameView.render().el);
        }
    });
    return GameListView;
});

define('app/views/index',[
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/collections/games',
  'app/collections/desired-games',
  'app/views/game-count',
  'app/views/desired-game-list',
  'app/views/game-list',
  'app/util/current-user',
], function($, _, Backbone, GameCollection, DesiredGameCollection, GameCountView, DesiredGameListView, GameListView, currentUser){
         
    var IndexView = Backbone.View.extend({
        el: $("#app"),
        template: _.template($('#games').html()),
        events: {
        },
        initialize: function() {
            this.render();
        },
        render: function() {
            this.$el.html(this.template({}));
            var gameCollection = new GameCollection();
            var desiredGameCollection = new DesiredGameCollection();

            
            var gameCountView = new GameCountView({'model': gameCollection});
            var desiredGameListView = new DesiredGameListView({'model': desiredGameCollection});
            var gameListView = new GameListView({'model': gameCollection});

            this.$("#game-list").append(gameListView.el);
            this.$("#desired-game-list").append(desiredGameListView.el);
            this.$("#game-count").append(gameCountView.el);
            
            desiredGameCollection.fetch();
            currentUser.fetch();
            gameCollection.fetch();
        },
    });

    return IndexView;

});

define('app/util/prefix',[], function(){
    if (window.location.pathname.substring(0, 12) == '/app_dev.php') {
        return 'app_dev.php';
    }
    if (window.location.pathname.substring(0, 13) == '/app_test.php') {
        return 'app_test.php';
    }
    return '';
});

define('app/router',[
  'backbone',
  'app/views/index',
  'app/util/prefix'
], function(Backbone, IndexView, prefix) {
    
    Router = Backbone.Router.extend({
        routes: function(){
            var routes = new Array();
            routes[prefix + ""] = "index";
            routes[prefix + "/"] = "index";
            return routes;
        },
        index: function() {
            var indexView = new IndexView({});
        }
    });
    
    var initialize = function() {
//        var user = new $.ventoonirico.CurrentUser();
        var router = new Router();
        Backbone.history.start({
            pushState: true
        });
    }

    return {
        initialize: initialize
    };

});

define('app/app',[
  'app/router',
], function(Router){

    var initialize = function() {
        Router.initialize();
    };

    return {
        initialize: initialize
    };
});

require.config({
    paths: {
        'jquery': 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min',
        'json2': 'http://ajax.cdnjs.com/ajax/libs/json2/20110223/json2',
        'jquery-ui': '../jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min',
        'underscore': 'libs/underscore/underscore',
        'backbone': 'libs/backbone/backbone',
        'backbone-relational': 'libs/backbone/backbone-relational',
        
        'backbone-loader': 'libs/loader/backbone',
        'jquery-loader': 'libs/loader/jquery',
        
        'eventie': 'libs/bower_components/eventie',
        'doc-ready': 'libs/bower_components/doc-ready',
        'eventEmitter': 'libs/bower_components/eventEmitter',
        'get-style-property': 'libs/bower_components/get-style-property',
        'get-size': 'libs/bower_components/get-size',
        'matches-selector': 'libs/bower_components/matches-selector',
        'outlayer': 'libs/bower_components/outlayer',
        'masonry': 'libs/bower_components/masonry',
    },
    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        },
        'backbone-relational': {
            deps: ['backbone'],
            exports: 'Backbone'
        }
    }

});

require([
    'app/app',
], function(App) {
    App.initialize();
});

define("main", function(){});
