class Noticias {
    constructor(urlFeed) {
        this.urlFeed = urlFeed;
    }

    crearSeccionNoticias() {
        const $section = $('<section>').attr('title', 'Noticias sobre Muros del Nalón');
        const $titulo = $('<h2>').text('Noticias sobre el concejo y alrededores');
        const $lista = $('<ul>');
        this.$lista = $lista;

        $section.append($titulo, $lista);
        $('main').append($section);

        this.cargarNoticias();
    }

    cargarNoticias() {
        const urlProxy = 'https://api.allorigins.win/get?url=' + encodeURIComponent(this.urlFeed);

        $.getJSON(urlProxy, (data) => {
            const xml = $.parseXML(data.contents);
            const $items = $(xml).find('item').slice(0, 5); // Solo 5 noticias
            $items.each((i, item) => {
                const $item = $(item);
                const titulo = $item.find('title').text();
                const enlace = $item.find('link').text();
                const $li = $('<li>');
                const $a = $('<a>').attr('href', enlace).attr('target', '_blank').text(titulo);
                $li.append($a);
                this.$lista.append($li);
            });
        });
    }
}

$(function() {
    // ...carrusel existente...

    // RSS de ejemplo: noticias de Asturias en La Nueva España
    const urlRSS = 'https://www.lne.es/rss/asturias';
    const noticias = new Noticias(urlRSS);
    noticias.crearSeccionNoticias();
});