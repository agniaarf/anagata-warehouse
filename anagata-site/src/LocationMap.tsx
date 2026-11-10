import { useEffect, useRef } from 'react'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import type { WarehouseLocation } from './App'

type Props = { locations: WarehouseLocation[]; selected: string | null; onSelect: (name: string) => void }

export function LocationMap({ locations, selected, onSelect }: Props) {
  const container = useRef<HTMLDivElement>(null)
  const mapRef = useRef<L.Map | null>(null)
  const markersRef = useRef(new Map<string, L.CircleMarker>())

  useEffect(() => {
    if (!container.current) return
    const map = L.map(container.current, { scrollWheelZoom: false, dragging: !L.Browser.mobile, zoomControl: true })
    mapRef.current = map
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      maxZoom: 18,
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
    }).addTo(map)
    L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
      maxZoom: 18,
      attribution: 'Labels &copy; Esri',
    }).addTo(map)
    const markers = markersRef.current
    locations.forEach(location => {
      if (location.status === 'coming' || typeof location.lat !== 'number' || typeof location.lng !== 'number') return
      const label = document.createElement('span')
      label.textContent = location.name
      const marker = L.circleMarker([location.lat, location.lng], { radius: 9, color: '#ffffff', weight: 3, fillColor: '#7b2d9f', fillOpacity: 1 })
        .addTo(map).bindTooltip(label, { direction: 'top', offset: [0, -8] })
        .on('click', () => onSelect(location.name))
      markers.set(location.name, marker)
    })
    const frameId = requestAnimationFrame(() => { map.invalidateSize(); map.setView([-3, 118], 4) })
    const observer = new ResizeObserver(() => map.invalidateSize())
    observer.observe(container.current)
    return () => { cancelAnimationFrame(frameId); observer.disconnect(); markers.clear(); map.remove(); mapRef.current = null }
  }, [locations, onSelect])

  useEffect(() => {
    if (!selected || !mapRef.current) return
    const marker = markersRef.current.get(selected)
    if (marker) { mapRef.current.flyTo(marker.getLatLng(), 13, { duration: 0.6 }); marker.openTooltip() }
  }, [selected])

  return <div className="location-map-wrap"><div ref={container} className="location-map" aria-label="Peta lokasi gudang Anagata di Indonesia" /></div>
}
