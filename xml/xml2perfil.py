import xml.etree.ElementTree as ET
import os
import unicodedata

def normalize_filename(name):
    """
    Normaliza un nombre para usarlo como nombre de archivo eliminando acentos y caracteres especiales.
    """
    # Normalizar unicode y eliminar diacríticos (acentos)
    normalized = unicodedata.normalize('NFD', name)
    normalized = ''.join(c for c in normalized if not unicodedata.combining(c))
    
    # Reemplazar espacios por guiones bajos y eliminar caracteres no deseados
    normalized = normalized.replace(" ", "_").replace("/", "_").lower()
    normalized = ''.join(c for c in normalized if c.isalnum() or c == '_' or c == '-')
    
    return normalized

def generate_individual_svg(route_data, svg_file):
    """
    Genera un archivo SVG para una ruta individual usando solo su rango de distancias.
    
    Args:
        route_data: Datos de la ruta
        svg_file: Ruta donde se guardará el archivo SVG
    """
    svg_width = 800
    svg_height = 400
    padding = 50
    
    # Calcular los rangos específicos para esta ruta
    route_points = route_data["points"]
    min_distance = 0
    max_distance = route_points[-1][0] if route_points else 0
    
    # Encontrar min y max altitud para esta ruta
    altitudes = [p[1] for p in route_points]
    min_altitude = min(altitudes) if altitudes else 0
    max_altitude = max(altitudes) if altitudes else 10
    
    # Asegurar que haya un rango de altitud
    if max_altitude == min_altitude:
        min_altitude = max_altitude - 10
        
    # Añadir un 10% de margen al rango de altitud para mejor visualización
    altitude_range = max_altitude - min_altitude
    min_altitude = max(0, min_altitude - altitude_range * 0.1)
    max_altitude = max_altitude + altitude_range * 0.1
    
    svg_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{svg_width}" height="{svg_height}">
    <rect width="100%" height="100%" fill="#f0f0f0"/>
    <text x="{svg_width/2}" y="30" font-family="Arial" font-size="20" text-anchor="middle" fill="#333">Altimetría: {route_data["name"]}</text>
    <text x="{svg_width/2}" y="55" font-family="Arial" font-size="14" text-anchor="middle" fill="#666">Distancia total: {int(max_distance)}m</text>
'''
    
    # Dibujar ejes
    svg_content += f'''
    <!-- Eje X -->
    <line x1="{padding}" y1="{svg_height-padding}" x2="{svg_width-padding}" y2="{svg_height-padding}" stroke="#333" stroke-width="2"/>
    <text x="{svg_width/2}" y="{svg_height-10}" font-family="Arial" font-size="12" text-anchor="middle" fill="#333">Distancia (m)</text>
    
    <!-- Eje Y -->
    <line x1="{padding}" y1="{svg_height-padding}" x2="{padding}" y2="{padding}" stroke="#333" stroke-width="2"/>
    <text x="15" y="{svg_height/2}" font-family="Arial" font-size="12" text-anchor="middle" fill="#333" transform="rotate(270, 15, {svg_height/2})">Altitud (m)</text>
'''
    
    # Etiquetas del eje X
    for i in range(5):
        x_val = min_distance + (max_distance - min_distance) * i / 4
        x_pos = padding + (svg_width - 2 * padding) * i / 4
        svg_content += f'''
    <line x1="{x_pos}" y1="{svg_height-padding}" x2="{x_pos}" y2="{svg_height-padding+5}" stroke="#333" stroke-width="1"/>
    <text x="{x_pos}" y="{svg_height-padding+20}" font-family="Arial" font-size="10" text-anchor="middle" fill="#333">{int(x_val)}</text>
'''
    
    # Etiquetas del eje Y
    for i in range(5):
        y_val = min_altitude + (max_altitude - min_altitude) * i / 4
        y_pos = svg_height - padding - (svg_height - 2 * padding) * i / 4
        svg_content += f'''
    <line x1="{padding}" y1="{y_pos}" x2="{padding-5}" y2="{y_pos}" stroke="#333" stroke-width="1"/>
    <text x="{padding-10}" y="{y_pos+5}" font-family="Arial" font-size="10" text-anchor="end" fill="#333">{int(y_val)}</text>
'''
    
    # Crear polilínea para la ruta
    polyline_points = []
    for distance, altitude in route_points:
        # Convertir a coordenadas SVG
        x = padding + (distance - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
        y = svg_height - padding - (altitude - min_altitude) / (max_altitude - min_altitude) * (svg_height - 2 * padding) if max_altitude > min_altitude else svg_height - padding
        polyline_points.append(f"{x},{y}")
    
    if polyline_points:
        # Añadir puntos para cerrar el polígono
        first_x = padding + (route_points[0][0] - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
        last_x = padding + (route_points[-1][0] - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else svg_width - padding
        
        # Crear un polígono cerrado en lugar de una polilínea abierta
        polygon_points = polyline_points[:]
        polygon_points.append(f"{last_x},{svg_height-padding}")
        polygon_points.append(f"{first_x},{svg_height-padding}")
        
        # Añadir el polígono para el área bajo la línea
        svg_content += f'''
    <polygon points="{' '.join(polygon_points)}" fill="{route_data["color"]}" fill-opacity="0.2" stroke="none"/>
'''
        
        # Añadir la polilínea para la ruta
        svg_content += f'''
    <polyline points="{' '.join(polyline_points)}" fill="none" stroke="{route_data["color"]}" stroke-width="3"/>
'''
        
        # Añadir círculos en los puntos y etiquetas
        for i, (distance, altitude) in enumerate(route_points):
            x = padding + (distance - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
            y = svg_height - padding - (altitude - min_altitude) / (max_altitude - min_altitude) * (svg_height - 2 * padding) if max_altitude > min_altitude else svg_height - padding
            
            # Añadir etiqueta con la altitud y distancia
            svg_content += f'''
    <circle cx="{x}" cy="{y}" r="4" fill="{route_data["color"]}" stroke="#fff" stroke-width="1"/>
    <text x="{x}" y="{y-10}" font-family="Arial" font-size="10" text-anchor="middle" fill="#333">{int(altitude)}m</text>
'''
            # Añadir nombre del hito si está disponible
            if i < len(route_data["hito_names"]):
                hito_name = route_data["hito_names"][i]
                if hito_name:
                    svg_content += f'''
    <text x="{x}" y="{y+20}" font-family="Arial" font-size="9" text-anchor="middle" fill="#333" transform="rotate(45, {x}, {y+20})">{hito_name}</text>
'''
    
    # Cerrar el SVG
    svg_content += '''
</svg>
'''
    
    # Guardar el archivo SVG
    with open(svg_file, 'w', encoding='utf-8') as f:
        f.write(svg_content)
    
    print(f"Archivo SVG individual generado: {svg_file}")

def generate_altimetry_svgs(xml_file, output_dir):
    """
    Genera archivos SVG individuales con la altimetría de cada ruta en el archivo XML.
    
    Args:
        xml_file: Ruta al archivo XML
        output_dir: Directorio donde se guardarán los archivos SVG individuales
    """
    # Cargar y parsear el archivo XML
    tree = ET.parse(xml_file)
    root = tree.getroot()
    
    # Colores para las diferentes rutas
    route_colors = ["#4285F4", "#DB4437", "#F4B400", "#0F9D58", "#9C27B0"]
    
    # Procesar cada ruta en el XML
    for i, ruta in enumerate(root.findall('ruta')):
        route_name = ruta.find('nombre').text
        color = route_colors[i % len(route_colors)]
        
        # Obtener los puntos para esta ruta
        points = []
        hito_names = []
        
        # Añadir punto inicial
        punto_inicio = ruta.find('puntoInicio')
        if punto_inicio is not None:
            coord_inicio = punto_inicio.find('coordenadas')
            if coord_inicio is not None and coord_inicio.find('altitud') is not None:
                alt_inicio = float(coord_inicio.find('altitud').text)
                # El primer punto está en distancia 0
                points.append((0, alt_inicio))
                lugar_inicio = punto_inicio.find('lugar')
                hito_names.append(lugar_inicio.text if lugar_inicio is not None else "Inicio")
        
        # Añadir hitos
        total_distance = 0
        for hito in ruta.findall('.//hito'):
            # Obtener distancia
            distancia_elem = hito.find('distancia')
            if distancia_elem is not None:
                distance = float(distancia_elem.text)
                total_distance += distance
                
                # Obtener altitud - Si no hay altitud, usamos la del punto inicial o un valor por defecto
                coord = hito.find('coordenadas')
                if coord is not None and coord.find('altitud') is not None:
                    alt = float(coord.find('altitud').text)
                else:
                    # Usar la altitud del punto anterior o un valor por defecto
                    alt = points[-1][1] if points else (alt_inicio if 'alt_inicio' in locals() else 0)
                
                points.append((total_distance, alt))
                
                # Obtener nombre del hito
                nombre_hito = hito.find('nombre')
                hito_names.append(nombre_hito.text if nombre_hito is not None else "")
        
        if points:
            route_data = {
                "name": route_name,
                "points": points,
                "hito_names": hito_names,
                "color": color
            }
            
            # Generar un SVG individual para esta ruta con su propio rango de distancias
            route_name_safe = normalize_filename(route_data["name"])
            individual_svg_file = os.path.join(output_dir, f"altimetria_{route_name_safe}.svg")
            generate_individual_svg(route_data, individual_svg_file)

def main():
    # Obtener la ruta del directorio actual
    current_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Rutas de los archivos
    xml_file = os.path.join(current_dir, "rutas.xml")
    output_dir = current_dir
    
    # Verificar que el archivo XML existe
    if not os.path.exists(xml_file):
        print(f"Error: El archivo {xml_file} no existe.")
        return
    
    # Generar los SVG individuales
    generate_altimetry_svgs(xml_file, output_dir)
    
    print("\nProceso completado.")
    print("Se han generado archivos SVG individuales para cada ruta.")
    print("\nPara convertir a PDF, abra los archivos SVG en un navegador y utilice la función de impresión para guardarlos como PDF.")

if __name__ == "__main__":
    main()