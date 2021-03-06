var happy = {
  // matches mm/dd/yyyy
  date: function (val) {
    return /^(?:[1-9]|10|11|12)\/(?:[1-9]|[12][0-9]|3[01])\/(?:\d{4})/.test(val);
  },
  selectorIsEmpty: function(arg) {
    var not_blank = jQuery.grep(jq15(arg), function(el, i) {
      var thisEl = jq15(el);
      if ((el.type == 'checkbox' || el.type == 'radio') && thisEl.filter(':checked').length == 0) {
        return false;
      }
      return !(thisEl.val() === '');
    });
    return (not_blank.length == 0);
  },
  requiredIfArgNotEmpty: function(val, arg) {
    if (!(val === '')) {
      return true;
    }
    return happy.selectorIsEmpty(arg);
  },
  email: function (val) {
    return /^(?:\w+\.?)*\w+@(?:\w+\.)+\w+$/.test(val);
  },
  alphaNumeric: function(val) {
    return /^([a-zA-Z0-9]+)$/.test(val);
  },
  numeric: function(val){
    return /^([0-9.]+)$/.test(val);
  },
  alpha: function(val) {
    //return /^([a-zA-Z\s]+)$/.test(val);
    return /^([\wáéíóúäëïöàèìòùÁÉÍÓÚñÑ\s]+)$/.test(val);
  },
  minLength: function (val, length) {
    return val.length >= length;
  },
  maxLength: function (val, length) {
    return val.length <= length;
  },
  equal: function (val1, val2) {
    return (val1 == val2);
  },
  nequal: function(val1, val2) {
	  return (val1 != val2);
  },
  emptyOrDate: function (val) {
    return (val === '' || happy.date(val));
  },
  emptyOrEmail: function (val) {
    return (val === '' || happy.email(val));
  }
};
