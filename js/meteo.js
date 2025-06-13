class Meteo {
    constructor() {
        this.lat = "43.5426";
        this.lon = "-6.1033";
        this.url = `https://api.open-meteo.com/v1/forecast?latitude=${this.lat}&longitude=${this.lon}&current=temperature_2m,wind_speed_10m&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m,precipitation&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=auto&forecast_days=7&lang=es`;
    }

    cargarPrediccion() {
        $.ajax({
            dataType: "json",
            url: this.url,
            method: 'GET',
            success: (datos) => {
                this.procesarDatosPrediccion(datos);
                console.log("Datos cargados correctamente");
            },
            error: (error) => {
                console.error("Error al cargar datos:", error);
                $('section').first().html('<p>Error al cargar el pronóstico del tiempo: ' + error.statusText + '</p>');
            }
        });
    }

    procesarDatosPrediccion(datos) {
        console.log("Procesando datos:", datos);
        
        const $seccion = $('section').first();
        $seccion.empty();
        
        if (!datos.daily || !datos.daily.time || datos.daily.time.length === 0) {
            $seccion.html('<p>No se pudieron cargar datos de pronóstico.</p>');
            return;
        }

        const wmoToIcon = {
            0: '01d', // Clear sky
            1: '02d', 2: '02d', 3: '03d', // Partly cloudy
            45: '50d', 48: '50d', // Fog
            51: '09d', 53: '09d', 55: '09d', // Drizzle
            56: '13d', 57: '13d', // Freezing Drizzle
            61: '10d', 63: '10d', 65: '10d', // Rain
            66: '13d', 67: '13d', // Freezing Rain
            71: '13d', 73: '13d', 75: '13d', // Snow
            77: '13d', // Snow grains
            80: '09d', 81: '09d', 82: '09d', // Rain showers
            85: '13d', 86: '13d', // Snow showers
            95: '11d', // Thunderstorm
            96: '11d', 99: '11d' // Thunderstorm with hail
        };

        const wmoToDesc = {
            0: 'Cielo despejado',
            1: 'Mayormente despejado', 2: 'Parcialmente nublado', 3: 'Nublado',
            45: 'Niebla', 48: 'Niebla escarcha',
            51: 'Llovizna ligera', 53: 'Llovizna moderada', 55: 'Llovizna intensa',
            56: 'Llovizna helada ligera', 57: 'Llovizna helada intensa',
            61: 'Lluvia ligera', 63: 'Lluvia moderada', 65: 'Lluvia intensa',
            66: 'Lluvia helada ligera', 67: 'Lluvia helada intensa',
            71: 'Nevada ligera', 73: 'Nevada moderada', 75: 'Nevada intensa',
            77: 'Granos de nieve',
            80: 'Chubascos ligeros', 81: 'Chubascos moderados', 82: 'Chubascos violentos',
            85: 'Chubascos de nieve ligeros', 86: 'Chubascos de nieve intensos',
            95: 'Tormenta',
            96: 'Tormenta con granizo ligero', 99: 'Tormenta con granizo fuerte'
        };
        
        const $h2 = $('<h2>').text('Pronóstico del Tiempo para Muros del Nalón');
        $seccion.append($h2);
        
        const $tabla = $('<table>');
        const $tbody = $('<tbody>');
        
        const totalDias = datos.daily.time.length;
        const diasPorFila = 4;
        const numFilas = Math.ceil(totalDias / diasPorFila);
        
        for (let fila = 0; fila < numFilas; fila++) {
            const $tr = $('<tr>');
            
            for (let col = 0; col < diasPorFila; col++) {
                const indice = fila * diasPorFila + col;
                
                if (indice >= totalDias) {
                    break;
                }
                
                const fecha = datos.daily.time[indice];
                const tempMax = datos.daily.temperature_2m_max[indice];
                const tempMin = datos.daily.temperature_2m_min[indice];
                const lluviaTotal = datos.daily.precipitation_sum[indice];
                const codigoTiempo = datos.daily.weather_code[indice];
                
                let humedadMedia = 0;
                let contadorHumedad = 0;
                
                for (let j = 0; j < datos.hourly.time.length; j++) {
                    if (datos.hourly.time[j].startsWith(fecha)) {
                        humedadMedia += datos.hourly.relative_humidity_2m[j];
                        contadorHumedad++;
                    }
                }
                
                humedadMedia = Math.round(humedadMedia / contadorHumedad);
                
                const fechaObj = new Date(fecha);
                const fechaFormateada = fechaObj.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    day: 'numeric', 
                    month: 'long' 
                });
                
                const icono = wmoToIcon[codigoTiempo] || '03d';
                const descripcion = wmoToDesc[codigoTiempo] || 'Sin datos';
                
                const $td = $('<td>');
                
                const $articulo = $('<article>');
                
                const $h3 = $('<h3>').text(fechaFormateada);
                $articulo.append($h3);
                
                const $figure = $('<figure>');
                const $img = $('<img>').attr({
                    'src': `https://openweathermap.org/img/wn/${icono}@2x.png`,
                    'alt': descripcion
                });
                $figure.append($img);
                $articulo.append($figure);
                
                const $pDesc = $('<p>').text(descripcion);
                $articulo.append($pDesc);
                
                const $dl = $('<dl>');
                
                $dl.append($('<dt>').text('🔥 Temperatura máxima:'));
                $dl.append($('<dd>').html(`<strong>${tempMax.toFixed(1)}°C</strong>`));
                
                $dl.append($('<dt>').text('❄️ Temperatura mínima:'));
                $dl.append($('<dd>').html(`<strong>${tempMin.toFixed(1)}°C</strong>`));
                
                $dl.append($('<dt>').text('💧 Humedad:'));
                $dl.append($('<dd>').html(`<strong>${humedadMedia}%</strong>`));
                
                $dl.append($('<dt>').text('☔ Lluvia:'));
                $dl.append($('<dd>').html(`<strong>${lluviaTotal.toFixed(1)} mm</strong>`));
                
                $articulo.append($dl);
                $td.append($articulo);
                $tr.append($td);
            }
            
            $tbody.append($tr);
        }
        
        $tabla.append($tbody);
        $seccion.append($tabla);
    }
}

$(document).ready(function() {
    const meteo = new Meteo();
    meteo.cargarPrediccion();
});