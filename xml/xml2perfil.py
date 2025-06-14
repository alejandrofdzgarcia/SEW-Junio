import xml.etree.ElementTree as ET
import os
import unicodedata

def normalize_filename(name):
    """Normaliza un nombre para usarlo como nombre de archivo"""
    normalized = unicodedata.normalize('NFD', name)
    normalized = ''.join(c for c in normalized if not unicodedata.combining(c))
    
    normalized = normalized.replace(" ", "_").replace("/", "_").lower()
    normalized = ''.join(c for c in normalized if c.isalnum() or c == '_' or c == '-')
    
    return normalized

def generate_individual_svg(route_data, svg_file):
    """Genera un archivo SVG para una ruta individual"""
    svg_width = 800
    svg_height = 400
    padding = 50
    
    route_points = route_data["points"]
    min_distance = 0
    max_distance = route_points[-1][0] if route_points else 0
    
    altitudes = [p[1] for p in route_points]
    min_altitude = min(altitudes) if altitudes else 0
    max_altitude = max(altitudes) if altitudes else 10
    
    if max_altitude == min_altitude:
        min_altitude = max_altitude - 10
        
    altitude_range = max_altitude - min_altitude
    min_altitude = max(0, min_altitude - altitude_range * 0.1)
    max_altitude = max_altitude + altitude_range * 0.1
    
    svg_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{svg_width}" height="{svg_height}">
    <rect width="100%" height="100%" fill="#f0f0f0"/>
    <text x="{svg_width/2}" y="30" font-family="Arial" font-size="20" text-anchor="middle" fill="#333">Altimetría: {route_data["name"]}</text>
    <text x="{svg_width/2}" y="55" font-family="Arial" font-size="14" text-anchor="middle" fill="#666">Distancia total: {int(max_distance)}m</text>
'''
    
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
        x = padding + (distance - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
        y = svg_height - padding - (altitude - min_altitude) / (max_altitude - min_altitude) * (svg_height - 2 * padding) if max_altitude > min_altitude else svg_height - padding
        polyline_points.append(f"{x},{y}")
    
    if polyline_points:
        first_x = padding + (route_points[0][0] - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
        last_x = padding + (route_points[-1][0] - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else svg_width - padding
        
        # Crear un polígono cerrado para el área bajo la línea
        polygon_points = polyline_points[:]
        polygon_points.append(f"{last_x},{svg_height-padding}")
        polygon_points.append(f"{first_x},{svg_height-padding}")
        
        svg_content += f'''
    <polygon points="{' '.join(polygon_points)}" fill="{route_data["color"]}" fill-opacity="0.2" stroke="none"/>
'''
        
        svg_content += f'''
    <polyline points="{' '.join(polyline_points)}" fill="none" stroke="{route_data["color"]}" stroke-width="3"/>
'''
        
        # Añadir círculos en los puntos y etiquetas
        for i, (distance, altitude) in enumerate(route_points):
            x = padding + (distance - min_distance) / (max_distance - min_distance) * (svg_width - 2 * padding) if max_distance > min_distance else padding
            y = svg_height - padding - (altitude - min_altitude) / (max_altitude - min_altitude) * (svg_height - 2 * padding) if max_altitude > min_altitude else svg_height - padding
            
            svg_content += f'''
    <circle cx="{x}" cy="{y}" r="4" fill="{route_data["color"]}" stroke="#fff" stroke-width="1"/>
    <text x="{x}" y="{y-10}" font-family="Arial" font-size="10" text-anchor="middle" fill="#333">{int(altitude)}m</text>
'''
            if i < len(route_data["hito_names"]):
                hito_name = route_data["hito_names"][i]
                if hito_name:
                    svg_content += f'''
    <text x="{x}" y="{y+20}" font-family="Arial" font-size="9" text-anchor="middle" fill="#333" transform="rotate(45, {x}, {y+20})">{hito_name}</text>
'''
    
    svg_content += '''
</svg>
'''
    
    with open(svg_file, 'w', encoding='utf-8') as f:
        f.write(svg_content)
    
    print(f"Archivo SVG individual generado: {svg_file}")

def generate_altimetry_svgs(xml_file, output_dir):
    """Genera archivos SVG con la altimetría de cada ruta"""
    tree = ET.parse(xml_file)
    root = tree.getroot()
    
    route_colors = ["#4285F4", "#DB4437", "#F4B400", "#0F9D58", "#9C27B0"]
    
    for i, ruta in enumerate(root.findall('ruta')):
        route_name = ruta.find('nombre').text
        color = route_colors[i % len(route_colors)]
        
        points = []
        hito_names = []
        
        # Añadir punto inicial
        punto_inicio = ruta.find('puntoInicio')
        if punto_inicio is not None:
            coord_inicio = punto_inicio.find('coordenadas')
            if coord_inicio is not None and coord_inicio.find('altitud') is not None:
                alt_inicio = float(coord_inicio.find('altitud').text)
                points.append((0, alt_inicio))
                lugar_inicio = punto_inicio.find('lugar')
                hito_names.append(lugar_inicio.text if lugar_inicio is not None else "Inicio")
        
        # Añadir hitos
        total_distance = 0
        for hito in ruta.findall('.//hito'):
            distancia_elem = hito.find('distancia')
            if distancia_elem is not None:
                distance = float(distancia_elem.text)
                total_distance += distance
                
                coord = hito.find('coordenadas')
                if coord is not None and coord.find('altitud') is not None:
                    alt = float(coord.find('altitud').text)
                else:
                    alt = points[-1][1] if points else (alt_inicio if 'alt_inicio' in locals() else 0)
                
                points.append((total_distance, alt))
                
                nombre_hito = hito.find('nombre')
                hito_names.append(nombre_hito.text if nombre_hito is not None else "")
        
        if points:
            route_data = {
                "name": route_name,
                "points": points,
                "hito_names": hito_names,
                "color": color
            }
            
            route_name_safe = normalize_filename(route_data["name"])
            individual_svg_file = os.path.join(output_dir, f"altimetria_{route_name_safe}.svg")
            generate_individual_svg(route_data, individual_svg_file)

def main():
    current_dir = os.path.dirname(os.path.abspath(__file__))
    
    xml_file = os.path.join(current_dir, "rutas.xml")
    output_dir = current_dir
    
    if not os.path.exists(xml_file):
        print(f"Error: El archivo {xml_file} no existe.")
        return
    
    generate_altimetry_svgs(xml_file, output_dir)
    
    print("\nProceso completado.")
    print("Se han generado archivos SVG individuales para cada ruta.")

if __name__ == "__main__":
    main()