class Meteo {
    constructor() {
        this.apikey = "8db286c3c29b4989e0782d0d687a41d4";
        this.lat = "43.5426";
        this.lon = "-6.1033";
        this.unidades = "&units=metric";
        this.idioma = "&lang=es";
        this.formato = "&mode=xml";
        this.url = `https://api.openweathermap.org/data/2.5/forecast?lat=${this.lat}&lon=${this.lon}${this.formato}${this.unidades}${this.idioma}&appid=${this.apikey}`;
    }

    cargarPrediccion() {
        $.ajax({
            dataType: "xml",
            url: this.url,
            method: 'GET',
            success: (datos) => {
                this.procesarDatosPrediccion(datos);
            },
            error: (error) => {
                $('section').first().html('<p>Error al cargar el pronóstico del tiempo.</p>');
            }
        });
    }

    procesarDatosPrediccion(datos) {
        const $seccion = $('section').first();
        $seccion.empty();
        
        const prediccionesPorDia = {};
        
        $(datos).find('time').each(function() {
            const fecha = $(this).attr('from').split('T')[0];
            
            if (!prediccionesPorDia[fecha]) {
                prediccionesPorDia[fecha] = {
                    tempMax: -100,
                    tempMin: 100,
                    humedadTotal: 0,
                    lluviaTotal: 0,
                    icono: '',
                    descripcion: '',
                    contador: 0
                };
            }
            
            const temp = parseFloat($(this).find('temperature').attr('value'));
            const humedad = parseFloat($(this).find('humidity').attr('value'));
            const lluvia = parseFloat($(this).find('precipitation').attr('value') || 0);
            
            const hora = parseInt($(this).attr('from').split('T')[1].split(':')[0]);
            if (!prediccionesPorDia[fecha].icono || (hora >= 12 && hora <= 15)) {
                prediccionesPorDia[fecha].icono = $(this).find('symbol').attr('var');
                prediccionesPorDia[fecha].descripcion = $(this).find('symbol').attr('name');
            }
            
            prediccionesPorDia[fecha].tempMax = Math.max(prediccionesPorDia[fecha].tempMax, temp);
            prediccionesPorDia[fecha].tempMin = Math.min(prediccionesPorDia[fecha].tempMin, temp);
            prediccionesPorDia[fecha].humedadTotal += humedad;
            prediccionesPorDia[fecha].lluviaTotal += lluvia;
            prediccionesPorDia[fecha].contador++;
        });
        
        const fechas = Object.keys(prediccionesPorDia).sort().slice(0, 5);
        
        fechas.forEach(fecha => {
            const datos = prediccionesPorDia[fecha];
            const humedadMedia = Math.round(datos.humedadTotal / datos.contador);
            
            // Formato de fecha: "lunes, 26 de mayo"
            const fechaObj = new Date(fecha);
            const fechaFormateada = fechaObj.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long' 
            });
            
            const $articulo = $('<article>');
            const $h3 = $('<h3>').text(fechaFormateada);
            $articulo.append($h3);
            
            const $divIcono = $('<div>');
            const $img = $('<img>').attr({
                'src': `https://openweathermap.org/img/wn/${datos.icono}@2x.png`,
                'alt': datos.descripcion
            });
            $divIcono.append($img);
            $articulo.append($divIcono);
            
            const $pDesc = $('<p>').text(datos.descripcion);
            $articulo.append($pDesc);
            
            const $divDatos = $('<div>');
            const $pTempMax = $('<p>');
            $pTempMax.html(`🔥 Máx: <span>${datos.tempMax.toFixed(1)}°C</span>`);
            $divDatos.append($pTempMax);
            
            const $pTempMin = $('<p>');
            $pTempMin.html(`❄️ Mín: <span>${datos.tempMin.toFixed(1)}°C</span>`);
            $divDatos.append($pTempMin);
            
            const $pHum = $('<p>');
            $pHum.html(`💧 Humedad: <span>${humedadMedia}%</span>`);
            $divDatos.append($pHum);
            
            const $pLluvia = $('<p>');
            $pLluvia.html(`☔ Lluvia: <span>${datos.lluviaTotal.toFixed(1)} mm</span>`);
            $divDatos.append($pLluvia);
            
            $articulo.append($divDatos);
            $seccion.append($articulo);
        });
    }
}

$(document).ready(function() {
    const meteo = new Meteo();
    meteo.cargarPrediccion();
});