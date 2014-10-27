$.editable.addInputType("datepicker", {
	element:  function(settings, original) {
        var input = $('<input />');
        if (settings.width  != 'none') { input.width(settings.width);  }
        if (settings.height != 'none') { input.height(settings.height); }
        input.attr('autocomplete','off');
		$(this).append(input);
		return(input);
	},
	plugin:  function(settings, original) {
		var form = this;
		$("input", this).filter(":text").datepicker({
			onSelect: function(dateText) { $(this).hide(); $(form).trigger("submit"); }
		});
	}
});
