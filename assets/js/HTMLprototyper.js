var HTMLprototyper = (function ($) {
    var _dataBar,
    $bar,
    _templates,
    _newFileTemplate,
    _currentFile;
    /**
     * Obtiene datos principales de la barra y otros
     * necesarios para el funcionamiento
     * @return {void}
     */
    var _barInit = function () {
        _currentFile = location.href.split('/').slice(-1)[0];
        if(_currentFile === '') {
            _currentFile = 'index.html';
        }
        $.get('../../project.php?bar', function (data) {
            _dataBar = JSON.parse(data);
            _createBar();
        });
    };
    /**
     * Se encarga de crear e inicializar la barra
     * @return {void}
     */
    var _createBar = function () {
        // Obtenemos el template de la barra
        $.ajax({
            url: '../../templates/HTMLprototyper/template-bar.html',
            cache: false,
            success: function (data) {
                // Agregamos la barra al DOM vacia
                $('body').prepend(data);
                // Listamos los archivos y los agregamos al <select>
                _addFiles();
                // Remplazamos los textos
                data = _replaceBarTexts();
                // Guardamos la barra para trabajarla mas adelante
                $bar = $('body').find('.HTMLprototyper-bar');
                // Remplazamos el HTML con los nuevos textos
                $bar.html(data);
                // Añadimos funcionalidad cambio de archivo
                _fileEvent();
                // Añadimos funcionalidad a los botones
                _buttonsEvents();
                // Mostramos la barra
                $('body').addClass('HTMLprototyper-bar-open');
                $bar.show();
            }
        });
    };
    /**
     * Construye el listado de archivos y selecciona
     * en la lista el archivo con el que se esta trabajando
     */
    var _addFiles = function () {
        var fileSelect = $('.HTMLprototyper-bar').find('select'),
        files = _dataBar.files;
        for (var file in files) {
            file = files[file];
            if (_currentFile === file || (_currentFile === '' && file === 'index.html')) {
                fileSelect.append($('<option selected="selected"></option>').val(file).html(file));
            } else {
                fileSelect.append($('<option></option>').val(file).html(file));
            }
        }
    };
    /**
     * Remplaza los textos de la barra por los correspondientes
     * de acuerdo al lenguaje
     * @return {void}
     */
    var _replaceBarTexts = function () {
        // Obtenemos el HTML de la barra
        var data = $('body').find('.HTMLprototyper-bar').html();
        // Esto debería ser refactorizado en un futuro, está repetitivo
        var lang = _dataBar.lang,
        fileMetaData = _fileMetaData();
        data = data.replace('{new_file}', lang.new_file);
        data = data.replace('{copy_file}', lang.copy_file);
        data = data.replace('{save}', lang.save);
        data = data.replace('{modified}', lang.modified);
        data = data.replace('{modified-date}', fileMetaData[2]);
        return data;
    };
    /**
     * Obtiene meta-data del archivo actual
     * @return {array} Meta-data
     */
    var _fileMetaData = function () {
        var files = _dataBar.metadata.files;
        for(file in files) {
            file = files[file];
            if (_currentFile === file[0] || (_currentFile === '' && file[0] === 'index.html')) {
                return file;
            }
        }
    }
    /**
     * Se encarga de cambiar de URL al seleccionar un archivo
     * @return {void}
     */
    var _fileEvent = function () {
        var fileSelect = $('.HTMLprototyper-bar').find('select').on('change', function () {
            document.location.href = this.value;
        });
    };
    /**
     * Asigna funcionalidad a los botones de la barra
     * @return {void}
     */
    var _buttonsEvents = function () {
        $bar.find('button[data-role="new"]').on('click', function () {
            _newFileEvent();
        });
        $bar.find('button[data-role="copy"]').on('click', function () {
            _copyFileEvent();
        });
        $bar.find('button[data-role="save"]').on('click', function () {
            _saveFileEvent();
        });
    };
    /**
     * Obtiene lista de plantillas disponibles
     * @return {void}
     */
    var _getTemplates = function () {
        $.get('../../project.php?templates', function (data) {
            _templates = JSON.parse(data);
            _templates = _templates.templates;
        });
    };
    /**
     * Obtiene plantilla de nuevo archivo
     * @return {void}
     */
    var _newFileTemplate = function() {
        $.get('../../templates/HTMLprototyper/new-file.html', function (data) {
            _newFileTemplate = data;
        });
    };
    /**
     * Evento que maneja la creación de un nuevo archivo
     * @return {void}
     */
    var _newFileEvent = function () {
        // Cargamos la plantilla
        Modal.load(_newFileTemplate);
        // Agregamos las plantillas existente a la modal
        var $template = $('#HTMLprototyper-modal').find('.HTMLprototyper-templates ul');
        for (var template in _templates) {
            template = _templates[template];
            $template.append('<li data-template="' + template.template + '"><span>' + template.template + '</span><img src="' + template.image + '"></li>');
        }
        // Capturamos el evento de selección de plantilla
        $template.delegate('li', 'click', function () {
            var template = $(this).data('template'),
            fileName = prompt(_dataBar.lang.js_new_file_name);
            // Si el nombre no está vacío y no tiene caracateres extraños
            if ($.trim(fileName) !== '' && fileName.match(/^[a-z0-9\-\_]+$/i)) {
                $.get('../../project.php?newFile&fileName=' + fileName + '&template=' + template, function (data) {
                    data = JSON.parse(data);
                    if (data.error === false) {
                        document.location.href = fileName + '.html';
                    } else {
                        alert(data.msg);
                    }
                });
            } else {
                alert(_dataBar.lang.js_alphanumeric);
            }
        });
        // Abrimos la modal
        Modal.open();
    };
    var _copyFileEvent = function () {
        newFileName = prompt(_dataBar.lang.js_new_file_name);
        // Si el nombre no está vacío y no tiene caracateres extraños
        if ($.trim(newFileName) !== '' && newFileName.match(/^[a-z0-9\-\_]+$/i)) {
            $.get('../../project.php?copyFile&fileName=' + _currentFile + '&newFileName=' + newFileName, function (data) {
                data = JSON.parse(data);
                if (data.error === false) {
                    document.location.href = newFileName + '.html';
                } else {
                    alert(data.msg);
                }
            });
        } else {
            alert(_dataBar.lang.js_alphanumeric);
        }
    };
    var _saveFileEvent = function () {
        var $html = $('html').clone();
        $html.find('body').removeClass('HTMLprototyper-bar-open');
        $html.find('#HTMLprototyper-bar').remove();
        $html.find('#HTMLprototyper-modal').remove();
        // Esto viene de una extension de Chrome, la eliminamos por
        // si alguien más también la tiene
        $html.find('#window-resizer-tooltip').remove();
        $.post('../../project.php', {save: true, html: $html.html(), fileName: _currentFile}, function (data) {
            // Nada que hacer por el momento :P
        });
    };

    return {
        init: function () {
            var that = this;
            // Obtenemos lista de plantillas
            _getTemplates();
            // Obtenemos plantilla GUI para nuevo archivo
            // Así la tenemos pre-cargada
            _newFileTemplate();
            // Creamos la barra
            _barInit();
        },
    };
})(jQuery);

var Modal = (function ($) {
    /**
     * Asigna eventos a la ventana modal
     * @return {void}
     */
    var _events = function () {
        $('#HTMLprototyper-modal').find('.HTMLprototyper-modal-content').on('click', _eventClose).keyup(_eventClose);
    };
    /**
     * Cierra le ventana modal
     * @param  {object} event
     * @return {void}
     */
    var _eventClose = function (event) {
        console.log(event);
    };
    /**
     * Revisa si el modal ya existe
     * @return {boolean}
     */
    var _exists = function () {
        if ($('#HTMLprototyper-modal').length) {
            return true;
        }
        return false;
    };
    return {
        load: function (content) {
            // Revisamos si existe una modal en el documento, si no existe la creamos
            if (!_exists()) {
                // Creamos la modal, parte escondidad, luego hay que mostrarla
                var modalDom = '<div id="HTMLprototyper-modal" class="HTMLprototyper-modal-overlay"><div class="HTMLprototyper-modal-content"></div></div>';
                $('body').prepend(modalDom);
                // Asigmanos comportamiendo al modal
                _events();
            }
            // Cargamos el contenido enviado si existe
            if (content !== undefined) {
                $('#HTMLprototyper-modal').find('.HTMLprototyper-modal-content').prepend(content);
            }

        },
        open: function (content) {
            // Cargamos el contenido enviado
            this.load(content);
            // Mostramos la modal
            $('#HTMLprototyper-modal').show();
        },
        close: function () {

        }
    };
})(jQuery);

$(document).ready(function(){
    HTMLprototyper.init();
});
