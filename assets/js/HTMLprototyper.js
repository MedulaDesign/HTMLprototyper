var HTMLprototyper = (function ($) {
	var _dataBar;
	var _createBar = function () {
		// Obtenemos el template de la barra
		$.get('../../templates/HTMLprototyper/template-bar.html', function (data) {
			// Agregamos la barra al DOM vacia
			$('body').prepend(data);
			// Listamos los archivos y los agregamos al <select>
			_addFiles();
			// Remplazamos los textos
			data = _replaceLang();
			// Remplazamos el HTML con los nuevos textos
			$('.HTMLprototyper-bar').html(data);
			// Añadimos funcionalidad cambio de archivo
			_fileEvent();
			// Añadimos funcionalidad a los botones
			_buttonsEvents();
			// Mostramos la barra
			$('body').addClass('HTMLprototyper-bar-open');
			$('.HTMLprototyper-bar').show();
		});
	};
	var _addFiles = function () {
		var fileSelect = $('.HTMLprototyper-bar').find('select'),
		files = _dataBar.files,
		currentFile = location.href.split('/').slice(-1)[0];
		for (var file in files) {
			file = files[file];
			if (currentFile === file || (currentFile === '' && file === 'index.html')) {
				fileSelect.append($('<option selected="selected"></option>').val(file).html(file));
			} else {
				fileSelect.append($('<option></option>').val(file).html(file));
			}
		}
	};
	var _replaceLang = function () {
		// Obtenemos el HTML de la barra
		var data = $('body').find('.HTMLprototyper-bar').html();
		// Esto debería ser refactorizado en un futuro, está repetitivo
		var lang = _dataBar.lang;
		data = data.replace('{new_file}', lang.new_file);
		data = data.replace('{copy_file}', lang.copy_file);
		data = data.replace('{save}', lang.save);
		data = data.replace('{modified}', lang.modified);
		return data;
	};
	var _fileEvent = function () {
		var fileSelect = $('.HTMLprototyper-bar').find('select').on('change', function () {
			document.location.href = this.value;
		});
	};
	var _buttonsEvents = function () {

	};

	return {
		addBar: function () {
			var that = this;
			// Obtenemos los datos de la barra
			$.get('../../project.php?bar', function (data) {
				_dataBar = JSON.parse(data);
				_createBar();
			});
		},
	};
})(jQuery);

$(document).ready(function(){
	HTMLprototyper.addBar();
});
