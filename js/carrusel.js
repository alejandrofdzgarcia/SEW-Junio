class Carrusel {
    constructor(imagenes) {
        this.imagenes = imagenes;
        this.indice = 0;
    }

    crearCarrusel() {
        const $carrusel = $('<section>').attr('title', 'Carrusel de fotos');
        const $contenedor = $('<div>');

        const $img = $('<img>').attr('src', this.imagenes[this.indice]).attr('alt', 'Foto carrusel');
        
        const $botonPrev = $('<button>').text('Anterior');
        const $botonNext = $('<button>').text('Siguiente');

        this.$img = $img;

        $botonPrev.on('click', () => this.anterior());
        $botonNext.on('click', () => this.siguiente());

        $contenedor.append($img, $botonPrev, $botonNext);
        $carrusel.append($contenedor);

        $('main').append($carrusel);
    }

    mostrarImagen() {
        this.$img.attr('src', this.imagenes[this.indice]);
    }

    anterior() {
        this.indice = (this.indice - 1 + this.imagenes.length) % this.imagenes.length;
        this.mostrarImagen();
    }

    siguiente() {
        this.indice = (this.indice + 1) % this.imagenes.length;
        this.mostrarImagen();
    }
}

$(function() {
    const imagenes = [
        'multimedia/muros1.jpg',
        'multimedia/muros2.jpg',
        'multimedia/muros3.jpg',
        'multimedia/muros4.jpg', 
        'multimedia/mapaMuros.jpg'
    ];

    const carrusel = new Carrusel(imagenes);
    carrusel.crearCarrusel();
});