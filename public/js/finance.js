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
			},
			cookie: {
				path: '/',
				expires: 1
			}
		});
		
		return self;
	},
	
	loadTab: function(data){
		var self = this,
			options = self.options;
		var panel = $('.ui-tabs-panel:visible', self.element);
		var url = panel.attr('data-url');
		
		if(typeof(data) == 'object')
		{
			data.format = options.format;
		}
		else
		{
			data = {format: options.format};
		}
		
		if(options.month != '' && options.year != '')
		{
			data.month = options.month;
			data.year = options.year;
		}
		panel.load(url, data);
		panel.height($(window).height() - 185);
	},
	destroy: function() {
		this.element.tabs('destroy');
		$.Widget.prototype.destroy.apply(this, arguments);
	}
});

$.widget("ui.formDialog", {
	options: {
		url: '',
		title: '',
		width: 450,
		extraButtons: {},
		modal: true,
		submitName: 'Save'
	},
	_create: function() {
		var self = this,
			options = self.options;
		
		self.loadForm();
	},
	loadForm: function(){
		var self = this,
			options = self.options;
		var buttons = {};
		buttons[options.submitName] = function(event, ui){
			var form = $('form', self.box);
			$.post(options.url + '/format/json', form.serialize(), function(data){
				if(data.form != undefined && data.form !== '')
				{
					self.box.html(data.form);
					self._trigger('afterLoad', event);
				}
				else
				{
					processJsonResponse(data, options.afterSubmit);
					self.destroy();
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
		$.get(options.url, {format: 'json'}, function(data){
			self.box.html(data.form);
			self.box.dialog({
				bgiframe: true,
				resizable: true,
				width: options.width,
				modal: options.modal,
				title: options.title,
				buttons: buttons,
				close: function(event, ui) {
					self.destroy();
				},
				open: function(event, ui) {
					self._trigger('afterLoad', event)
				}
			});
		}, 'json');
	},
	destroy: function() {
		this.box.dialog('destroy');
		this.box.remove();
		$.Widget.prototype.destroy.apply(this, arguments);
	}
});

$.widget( "ui.formDialogButton", {
	options: {
		title: '',
		width: 550,
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
		
		options.url = self.element.attr('data-url');
		self.element.bind('click.formDialogButton',function(event){
			self.element.formDialog(options);
			return false;
		});
	},
	destroy: function() {
		this.element.formDialog('destroy');
		this.element.button('destroy');
		this.element.unbind('.formDialogButton');
		$.Widget.prototype.destroy.apply(this, arguments);
	}
});

$.datepicker.setDefaults({
	dateFormat: 'mm/dd/y'	
});


function refreshAllocations(data){
	var tabData = {};
	if(typeof(data.expense_id) != 'undefined')
	{
		tabData.expense_id = data.expense_id;
	}

	tabData.scroll = $('.fht_table_body').scrollTop();	
	$('#main-tabs').mainTabs('loadTab', tabData);
}

function wireAllocations(scroll)
{
	$('#new-expense').formDialogButton({
		buttonIcons: {primary: 'ui-icon-plusthick'},
		afterSubmit: refreshAllocations
	});

	$('#new-income').formDialogButton({
		buttonIcons: {primary: 'ui-icon-plusthick'},
		afterLoad: function(){
			$('input.datepicker', self.box).datepicker();
		},
		afterSubmit: refreshAllocations
	});

	$('#choose-expense').formDialogButton({
		width: 300,
		submitName: 'Choose',
		buttonIcons: {primary: 'ui-icon-check'},
		afterSubmit: refreshAllocations
	});
	
	$('.expense').click(function(){
		var expense = $(this);
		var options = {
			url: '/expense/edit/expense_id/' + expense.attr('data-expense'),
			afterSubmit: refreshAllocations
		};
		expense.formDialog(options);
		return false;
	});

	$('.income').click(function(){
		var income = $(this);
		var options = {
			url: '/income/edit/income_id/' + income.attr('data-income'),
			afterSubmit: refreshAllocations
		};
		income.formDialog(options);
		return false;
	});

	$('.allocation').click(function(){
		var allocation = $(this);
		var expense_id = allocation.attr('data-expense');
		var income_id = allocation.attr('data-income');
		var options = {
			url: '/allocation/edit/expense_id/' + expense_id + '/income_id/' + income_id,
			afterSubmit: refreshAllocations,
			extraButtons: {
				Delete: function(event, ui) {
					$.post('/allocation/delete/', {format: 'json', expense_id: expense_id, income_id: income_id}, function(data){
						allocation.formDialog('destroy');
						processJsonResponse(data, options.afterSubmit);
					}, 'json');
				}
			}
		};
		allocation.formDialog(options);
		return false;
	});

	$('#allocations-table').fixedHeaderTable({footer: true});
	$('.fht_table_body').scrollTop(scroll);
}

function refreshTransactions(data)
{
	var tabData = {};
	if(typeof(data.last_date) != 'undefined')
	{
		tabData.last_date = data.last_date;
	}

	if(typeof(data.last_expense) != 'undefined')
	{
		tabData.last_expense = data.last_expense;
	}
	
	if(typeof(data.page) != 'undefined')
	{
		tabData.page = data.page
	}
	
	$('#main-tabs').mainTabs('loadTab', tabData);
}

function wireTransactions(options)
{
	var editableUrl = '/transaction/edit-value/';
	
	$('#transaction input.datepicker').datepicker();

	if(options.import)
		importTransactions();

	$('#new-transaction').button().click(function(){
		var self = $(this);
		var form = $('#transaction');
		var url = self.attr('data-url');
		$.post(url + '/format/json', form.serialize(), function(data){
			if(data.form != undefined && data.form !== '')
			{
				$('#form-transaction').replaceWith(data.form);
				$('input.datepicker', form).datepicker();
			}
			else
			{
				refreshTransactions(data);
				processJsonResponse(data);
			}
		});
	});

	$('#upload-input').uploadify({
		uploader: '/uploadify/uploadify.swf',
		script: '/transaction/upload/',
		cancelImg: '/uploadify/cancel.png',
		auto: true,
		fileDataName: 'file',
		scriptData: options.scriptData,
		onError : function(event, queueId, fileObj, errorObj){
			console.log(errorObj);
		},
		onComplete: function(event, queueID, fileObj, response, data) { 
			if(response == 'import')
			{
				importTransactions();
			}
		}
	});

	$('.transaction .expense').each(function(){
		var transaction = $(this).parent().attr('data-transaction');
		$(this).editable(editableUrl, {
			data: options.expenseOptions,
			type: 'select',
			submit: 'Ok',
			submitdata: {
				format: 'html',
				transaction_id: transaction,
				column: 'expense_id'
			}
		});
	});

	$('.transaction .check_num').each(function(){
		var transaction = $(this).parent().attr('data-transaction');
		$(this).editable(editableUrl, {
			data: trimText,
			type: 'text',
			width: 50,
			submit: 'Ok',
			submitdata: {
				format: 'html',
				transaction_id: transaction,
				column: 'check_num'
			}
		});
	});

	$('.transaction .description').each(function(){
		var transaction = $(this).parent().attr('data-transaction');
		$(this).editable(editableUrl, {
			data: trimText,
			type: 'text',
			width: 500,
			submit: 'Ok',
			submitdata: {
				format: 'html',
				transaction_id: transaction,
				column: 'description'
			}
		});
	});

	$('.transaction .amount').each(function(){
		var transaction = $(this).parent().attr('data-transaction');
		$(this).editable(editableUrl, {
			data: trimCurrency,
			type: 'text',
			width: 75,
			submit: 'Ok',
			submitdata: {
				format: 'html',
				transaction_id: transaction,
				column: 'amount'
			}
		});
	});
	
	$('.transaction .date').each(function(){
		var transaction = $(this).parent().attr('data-transaction');
		$(this).editable(editableUrl, {
			type: 'datepicker',
			width: 75,
			submit: 'Ok',
			submitdata: {
				format: 'html',
				transaction_id: transaction,
				column: 'date'
			}
		});
	});
	
	$('.transaction .delete').each(function(){
		var button = $(this);
		var url = button.attr('data-url');
		button.button({
			icons: {
				primary: 'ui-icon-trash'
			},
			text: false
		});
		
		button.click(function(){
			$.post(url, {format: 'json'}, function(data){
				refreshTransactions(data);
				processJsonResponse(data);
			});
		});
	});
	
	$('.paginationControl a').click(function(){
		var url = $(this).attr('href');
		var parts = url.split('/');
		refreshTransactions({page: parts[4]});
		return false;
	});
	
	$('#transaction-table').height($('#transactions').height());
}

function importTransactions()
{
	var box = $('<div></div>');
	box.load('/transaction/import-form', {format: 'html'}, function(){
		box.dialog({
			modal: true,
			width: 850,
			buttons: {
				Continue: function(){
					var form = $('#import');
					$.post('/transaction/import/format/json', form.serialize(), function(data){
						processJsonResponse(data);
						box.dialog('destroy');
						box.remove()
						refreshTransactions(data);
					}, 'json');
				},
				Cancel: function(){
					$(this).dialog('destroy');
					box.remove()
				}
			},
			close: function() {
				$(this).dialog('destroy');
				box.remove()
			}
		});
	});
}

function trimText(value, setting)
{
	var trimmed = $.trim(value);
	if(trimmed == '&nbsp;')
		trimmed = '';

	return trimmed	;
}

function trimCurrency(value, setting)
{
	value = $.trim(value);
	var reg = new RegExp('(\\$|\\,)');
	return value.replace(reg, '');
}

function wireIncomes(){
	$('.edit-recurring').formDialogButton({
		buttonIcons: {primary: 'ui-icon-pencil'},
		buttonText: false,
		afterLoad: function(){
			$('input.datepicker', self.box).datepicker();
		},
		afterSubmit: wireIncomes
	});
}

function wireCategories(){
	$('.edit-category').formDialogButton({
		buttonIcons: {primary: 'ui-icon-pencil'},
		buttonText: false,
		afterSubmit: wireCategories
	});
	
	$('#category-list').sortable({
		update: function(event, ui){
			var categories = $(this).sortable('serialize');
			$.post('/expense/order-categories/format/json', categories, function(data){
				processJsonResponse(data);
			}, 'json');
		}
	});
}

function wireSettings()
{
	wireIncomes();
	wireCategories();
	
	$('#new-recurring').formDialogButton({
		buttonIcons: {primary: 'ui-icon-plusthick'},
		afterLoad: function(){
			$('input.datepicker', self.box).datepicker();
		},
		afterSubmit: wireIncomes
	});
	
	$('#new-category').formDialogButton({
		buttonIcons: {primary: 'ui-icon-plusthick'},
		afterSubmit: wireCategories
	});
}

$(document).ready(function(){
	var MainTabs = $('#main-tabs').mainTabs();
	
	var dateSelector = $('#controls .date-selector');
	var defaultDate = dateSelector.attr('data-date');
	dateSelector.datepicker({
		onChangeMonthYear: function(year, month, inst) {
			MainTabs.mainTabs('option', 'month', month);
			MainTabs.mainTabs('option', 'year', year);
			MainTabs.mainTabs('loadTab');
		},
		defaultDate: defaultDate
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