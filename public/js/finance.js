if(!window.console)
{
	window.console = new function() {
		this.log = function(str) {};
		this.dir = function(str) {};
	};
}

function processJsonResponse(data, callback)
{
	if(typeof(data.error) != 'undefined' && data.error !== '')
	{
		alert(data.error);
		if(!data.noreturn)
		{
			return;
		}
	}

	if(typeof(data.content) == 'string')
	{
		if(typeof(data.replace) == 'string')
		{
			$(data.replace).replaceWith(data.content);
		}
		else if(typeof(data.append) == 'string')
		{
			$(data.append).append(data.content);
		}
		else if(typeof(data.refresh) == 'string')
		{
			$(data.refresh).html(data.content);
		}
	}

	if( typeof(callback) == 'function' )
	{
		callback(data);
	}
	else
	{
		if(typeof(data.redirect) != 'undefined' && data.redirect !== '')
		{
			window.location = data.redirect;
		}
	}

	if(typeof(data.notify) != 'undefined' && data.notify.length > 0)
	{
		$.each(data.notify, function(index,value){
			$.gritter.add(value);
		});
	}
}

$.widget( "ui.mainTabs", {
	options: {
		format: 'html',
		month: '',
		year: ''
	},
	
	_create: function(){
		var self = this,
			options = self.options;
		self.element.tabs({
			show: function(){
				self.loadTab();
			}
		});
		
		return self;
	},
	
	loadTab: function(){
		var self = this,
			options = self.options;
		var panel = $('.ui-tabs-panel:visible', self.element);
		var url = panel.attr('data-url');
		var data = {format: options.format};
		if(options.month != '' && options.year != '')
		{
			data.month = options.month;
			data.year = options.year;
		}
		panel.load(url, data);
		panel.height($(window).height() - 195);
	},
	destroy: function() {
		this.element.tabs('destroy');
		$.Widget.prototype.destroy.apply(this, arguments);
	}	
});

$.widget( "ui.formDialogButton", {
	options: {
		title: '',
		buttonIcons: {},
		buttonText: true,
		extraButtons: {},
		modal: true,
		submitName: 'Save'
	},
	
	_create: function() {
		var self = this,
			options = self.options;
		
		self.element.button({
			icons: options.buttonIcons,
			text: options.buttonText
		});
		
		self.element.bind('click.formDialogButton',function(event){
			self.loadForm(event);
			return false;
		});
	},
	loadForm: function(event){
		var self = this,
			options = self.options;
		var url = self.element.attr('data-url');
		var buttons = {};
		buttons[options.submitName] = function(event, ui){
			var form = $('form', self.box);
			$.post(url + '/format/json', form.serialize(), function(data){
				if(data.form != undefined && data.form !== '')
				{
					self.box.html(data.form);
					self._trigger('afterLoad', event);
				}
				else
				{
					self.box.dialog('close');
					processJsonResponse(data, options.afterSubmit);
				}
			}, 'json');
		};
		
		for(i in options.extraButtons)
		{
			buttons[i] = options.extraButtons[i];
		}
		
		buttons.Cancel = function() {
			self.box.dialog('close');
		};
		
		self.box = $('<div></div>');
		$.get(url, {format: 'json'}, function(data){
			self.box.html(data.form);
			self.box.dialog({
				bgiframe: true,
				resizable: true,
				width: 550,
				modal: options.modal,
				title: options.title,
				buttons: buttons,
				close: function(event, ui) {
					self.box.dialog('destroy');
					self.box.remove();
				},
				open: function(event, ui) {
					self._trigger('afterLoad', event)
				}
			});
		}, 'json');
	},
	destroy: function() {
		this.element.button('destroy');
		this.element.unbind('.formDialogButton');
		if(typeof(this.box) != 'undefined')
		{
			this.box.dialog('destroy');
			this.box.remove();
		}
		$.Widget.prototype.destroy.apply(this, arguments);
	}
});

$.datepicker.setDefaults({
	dateFormat: 'm/d/y'	
});

$(document).ready(function(){
	var MainTabs = $('#main-tabs').mainTabs();
	
	$('#controls .date-selector').datepicker({
		onChangeMonthYear: function(year, month, inst) {
			MainTabs.mainTabs('option', 'month', month);
			MainTabs.mainTabs('option', 'year', year);
			MainTabs.mainTabs('loadTab');
		}
	});
	
	$('button.direct').each(function(){
		var self = $(this);
		self.button();
		var url = self.attr('data-url');
		if(typeof(url) == 'string' && url != "")
		{
			self.click(function(){
				window.location = url;
			});
		}
	});
});