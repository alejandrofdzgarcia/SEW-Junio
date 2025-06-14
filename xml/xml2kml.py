import xml.etree.ElementTree as ET
import os
import re
import unicodedata

def normalize_filename(name):
    """Normaliza un nombre para usarlo como nombre de archivo"""
    normalized = unicodedata.normalize('NFD', name)
    normalized = ''.join(c for c in normalized if not unicodedata.combining(c))
    
    normalized = normalized.replace(" ", "_").replace("/", "_").lower()
    normalized = ''.join(c for c in normalized if c.isalnum() or c == '_' or c == '-')
    
    return normalized

def create_kml_for_route(ruta, kml_file):
    """Create KML file for a single route"""
    nombre_ruta = ruta.find('nombre').text
    
    kml_content = '<?xml version="1.0" encoding="UTF-8"?>\n'
    kml_content += '<kml xmlns="http://www.opengis.net/kml/2.2">\n'
    kml_content += '  <Document>\n'
    kml_content += f'    <name>{nombre_ruta}</name>\n'
    kml_content += '    <description>Planimetría de la ruta</description>\n'
    
    kml_content += '    <Style id="routeStyle">\n'
    kml_content += '      <LineStyle>\n'
    kml_content += '        <color>ff0000ff</color>\n'
    kml_content += '        <width>4</width>\n'
    kml_content += '      </LineStyle>\n'
    kml_content += '    </Style>\n'
    
    kml_content += '    <Style id="hitoStyle">\n'
    kml_content += '      <IconStyle>\n'
    kml_content += '        <Icon>\n'
    kml_content += '          <href>http://maps.google.com/mapfiles/kml/paddle/red-circle.png</href>\n'
    kml_content += '        </Icon>\n'
    kml_content += '      </IconStyle>\n'
    kml_content += '    </Style>\n'
    
    punto_inicio = ruta.find('puntoInicio')
    if punto_inicio is not None:
        lugar_inicio = punto_inicio.find('lugar').text
        coords_inicio = punto_inicio.find('coordenadas')
        if coords_inicio is not None:
            lon_inicio = coords_inicio.find('longitud').text
            lat_inicio = coords_inicio.find('latitud').text
            alt_inicio = coords_inicio.find('altitud').text
            
            kml_content += f'    <Placemark>\n'
            kml_content += f'      <name>Inicio: {lugar_inicio}</name>\n'
            kml_content += f'      <styleUrl>#hitoStyle</styleUrl>\n'
            kml_content += f'      <Point>\n'
            kml_content += f'        <coordinates>{lon_inicio},{lat_inicio},{alt_inicio}</coordinates>\n'
            kml_content += f'      </Point>\n'
            kml_content += f'    </Placemark>\n'
    
    hitos = ruta.findall('./hitos/hito')
    
    for hito in hitos:
        nombre_hito = hito.find('nombre').text
        coords_hito = hito.find('coordenadas')
        if coords_hito is not None:
            lon_hito = coords_hito.find('longitud').text
            lat_hito = coords_hito.find('latitud').text
            alt_hito = coords_hito.find('altitud').text
            
            kml_content += f'    <Placemark>\n'
            kml_content += f'      <name>{nombre_hito}</name>\n'
            kml_content += f'      <description><![CDATA[{hito.find("descripcion").text.strip()}]]></description>\n'
            kml_content += f'      <styleUrl>#hitoStyle</styleUrl>\n'
            kml_content += f'      <Point>\n'
            kml_content += f'        <coordinates>{lon_hito},{lat_hito},{alt_hito}</coordinates>\n'
            kml_content += f'      </Point>\n'
            kml_content += f'    </Placemark>\n'
    
    # Create a linestring for the route (connecting all points)
    kml_content += f'    <Placemark>\n'
    kml_content += f'      <name>Recorrido: {nombre_ruta}</name>\n'
    kml_content += f'      <styleUrl>#routeStyle</styleUrl>\n'
    kml_content += f'      <LineString>\n'
    kml_content += f'        <tessellate>1</tessellate>\n'
    kml_content += f'        <coordinates>\n'
    
    punto_inicio = ruta.find('puntoInicio/coordenadas')
    if punto_inicio is not None:
        lon = punto_inicio.find('longitud').text
        lat = punto_inicio.find('latitud').text
        alt = punto_inicio.find('altitud').text
        kml_content += f'          {lon},{lat},{alt}\n'
    
    for hito in hitos:
        coords = hito.find('coordenadas')
        if coords is not None:
            lon = coords.find('longitud').text
            lat = coords.find('latitud').text
            alt = coords.find('altitud').text
            kml_content += f'          {lon},{lat},{alt}\n'
    
    kml_content += f'        </coordinates>\n'
    kml_content += f'      </LineString>\n'
    kml_content += f'    </Placemark>\n'
    
    kml_content += '  </Document>\n'
    kml_content += '</kml>'
    
    try:
        with open(kml_file, 'w', encoding='utf-8') as f:
            f.write(kml_content)
        print(f"KML file created successfully: {kml_file}")
        return True
    except Exception as e:
        print(f"Error writing KML file: {e}")
        return False

def create_kml_from_xml(xml_file, output_dir):
    """Convert XML routes file to individual KML files"""
    try:
        tree = ET.parse(xml_file)
        root = tree.getroot()
    except Exception as e:
        print(f"Error parsing XML file: {e}")
        return False
    
    os.makedirs(output_dir, exist_ok=True)
    
    rutas = root.findall('./ruta')
    for i, ruta in enumerate(rutas, 1):
        nombre_ruta = ruta.find('nombre').text
        
        normalized_name = normalize_filename(nombre_ruta)
        kml_file = os.path.join(output_dir, f'{normalized_name}.kml')
        
        create_kml_for_route(ruta, kml_file)
    
    return True

def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    xml_file = os.path.join(script_dir, 'rutas.xml')
    output_dir = os.path.join(script_dir)
    
    if not os.path.isfile(xml_file):
        print(f"XML file not found: {xml_file}")
        return
    
    create_kml_from_xml(xml_file, output_dir)
    
    print("Process completed!")
    print(f"KML files have been created in the directory: {output_dir}")
    print("You can view the KML files with Google Earth or other KML viewers.")

if __name__ == "__main__":
    main()