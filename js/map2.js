// JavaScript Document
// Gestion de la cartographie
// Ajot des bouees mobiles

var tmarkersMobiles=[]; // tableau des info sur les bouées mobiles à transformer en markers
var boueesmobiles;  // tableau des bouées mobiles intitialisé dans Ajax2.js
var mesMarkersMobiles;  // Objet des markers mobiles affichés sur la map  

//bouee8x6_vert_verte
    const svgIconVV = L.divIcon({
  html: `    
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#00AA00" stroke="#00CC00" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#00AA00"/>
<path d="M13 11L13 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#00AA00" stroke="#00CC00" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#00CC00"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#00CC00" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      

    

//bouee8x6_vert_rouge
    const svgIconVR = L.divIcon({
  html: `    
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#00AA00" stroke="#CC0000" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#00AA00"/>
<path d="M13 11L13 15" stroke="#CC0000" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#CC0000" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#00AA00" stroke="#CC0000" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#CC0000"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#CC0000" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      




//bouee8x6_bleu_verte
    const svgIconBV = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#0000CC" stroke="#00CC00" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#0000CC"/>
<path d="M13 11L13 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#0000CC" stroke="#00CC00" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#00CC00"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#00CC00" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      
   

    

//bouee8x6_bleu_rouge
    const svgIconBR = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#0000CC" stroke="#CC0000" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#0000CC"/>
<path d="M13 11L13 15" stroke="#CC0000" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#CC0000" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#0000CC" stroke="#CC0000" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#CC0000"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#CC0000" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      
      


//bouee8x6_violet_vert
    const svgIconPV = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#A825F8" stroke="#00CC00" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#A825F8"/>
<path d="M13 11L13 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#A825F8" stroke="#00CC00" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#00CC00"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#00CC00" stroke-width="0.5"/>
</svg> `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      
        



//bouee8x6_violet_rouge
    const svgIconPR = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#A825F8" stroke="#F71616" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#A825F8"/>
<path d="M13 11L13 15" stroke="#F71616" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#F71616" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#A825F8" stroke="#F71616" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#F71616"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#F71616" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10, -4]
});      
        


//bouee8x6_jaune_rouge
    const svgIconYR = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#DDCC00" stroke="#F71616" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#DDCC00"/>
<path d="M13 11L13 15" stroke="#F71616" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#F71616" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#DDCC00" stroke="#F71616" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#F71616"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#F71616" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      
   


// bouee8x6_jaune_vert
    const svgIconYV = L.divIcon({
  html: `  
<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.75 15.1277C12.75 15.3295 12.6307 15.5971 12.346 15.9014C12.0663 16.2001 11.6516 16.5068 11.1262 16.7832C10.0763 17.3356 8.61622 17.75 7 17.75C5.38378 17.75 3.92372 17.3356 2.87375 16.7832C2.34835 16.5068 1.9337 16.2001 1.65403 15.9014C1.36927 15.5971 1.25 15.3295 1.25 15.1277C1.25 14.9368 1.35844 14.7205 1.63213 14.4911C1.9045 14.2627 2.31354 14.0458 2.84091 13.8588C3.89344 13.4856 5.36388 13.25 7 13.25C8.63612 13.25 10.1066 13.4856 11.1591 13.8588C11.6865 14.0458 12.0955 14.2627 12.3679 14.4911C12.6416 14.7205 12.75 14.9368 12.75 15.1277Z" fill="#DDCC00" stroke="#00CC00" stroke-width="0.5"/>
<rect x="1" y="11" width="12" height="4" fill="#DDCC00"/>
<path d="M13 11L13 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M1 11L1 15" stroke="#00CC00" stroke-width="0.5"/>
<path d="M12.75 10.7021C12.75 10.8233 12.6552 11.0206 12.3661 11.2677C12.0881 11.5053 11.6735 11.751 11.1457 11.9732C10.0918 12.4167 8.62489 12.75 7 12.75C5.37511 12.75 3.90821 12.4167 2.85434 11.9732C2.3265 11.751 1.91187 11.5053 1.63394 11.2677C1.34477 11.0206 1.25 10.8233 1.25 10.7021C1.25 10.5948 1.32883 10.4363 1.61077 10.2472C1.88363 10.0642 2.29491 9.8896 2.82559 9.73905C3.88351 9.43893 5.35947 9.25 7 9.25C8.64053 9.25 10.1165 9.43893 11.1744 9.73905C11.7051 9.8896 12.1164 10.0642 12.3892 10.2472C12.6712 10.4363 12.75 10.5948 12.75 10.7021Z" fill="#DDCC00" stroke="#00CC00" stroke-width="0.5"/>
<path d="M7.17661 0.906853L15.9623 0.827442L7.1766 7L6.37598 3.61456L7.17661 0.906853Z" fill="#00CC00"/>
<path d="M6.7994 11.0494C6.79939 8.66522 6.76284 11.0494 6.7994 6.69999M7.40035 0.313025C7.0857 0.411906 7.22161 0.476549 6.92833 1.71472M6.92833 1.71472L15.4483 1.29564L6.7994 6.69999M6.92833 1.71472C6.711 2.63226 6.81457 4.89435 6.7994 6.69999" stroke="#00CC00" stroke-width="0.5"/>
</svg>
 `,
  className: "",
  iconSize: [20, 28],
  iconAnchor: [10,-4]
});      
   

// Ajouter à la map les bouees mobiles après chargement du fichier ./data/robonav_LePlessis_45*.json

// Reset des markers de bouées mobiles déjà présents et du contrôle associé.
// ----------------------------------
function resetMarkersBoueesMobiles(){
    //console.debug("resetMarkersBoueesMobiles"); 
    if ((tmarkersMobiles!==undefined) && (tmarkersMobiles.length>0)){
        if ((map !== undefined) && (mesMarkersMobiles !== undefined))
        {
            if (map.hasLayer(mesMarkersMobiles)){
                //console.debug("Remove Layer MarkersBoueesMobiles");
                map.removeLayer(mesMarkersMobiles);
            }
        }                                       
    }        
}

// ------------------------------
function addBoueesMobiles2Map(){
  
    if ((map!==undefined) && (boueesMobiles !== undefined) && (boueesMobiles.length>0)){
        resetMarkersBoueesMobiles();
        
        var myIcon;
        for (var index=0; index<boueesMobiles.length; index++){  
            var style='';
            var description="Bouée mobile N° "+boueesMobiles[index].id;
            var franchissement = true; // Tribord
            // Franchissement           
            if (boueesMobiles[index].fillcolor=="green"){
                franchissement = true;
                description+=" tribord";
            }
            else{
                franchissement = false;
                description+=" bâbord";   
            }             
            // Type de bouée
            if (boueesMobiles[index].color=="yellow"){
                description = description + '. Bouée de départ ';
                if (franchissement == true){
                    myIcon = svgIconYV;
                }  
                else{
                    myIcon = svgIconYR;
                }
            }
            else if (boueesMobiles[index].color=="blue"){
                description = description + '. Bouée d\'arrivée ';  
                if (franchissement == true){
                    myIcon = svgIconBV;
                }  
                else{
                    myIcon = svgIconBR;
                }          
            } 
            else if (boueesMobiles[index].color=="purple"){
                description = description + '. Porte ';  
                myIcon = myIconPurple;  
                if (franchissement == true){
                    myIcon = svgIconPV;
                }  
                else{
                    myIcon = svgIconPR;
                }                                   
            } 
            else{
                if (boueesMobiles[index].fillcolor=="green"){
                    myIcon = svgIconVV; 
                }
                else{
                    myIcon = svgIconVR; 
                }
            }   
            var latlng = L.latLng(boueesMobiles[index].lat,boueesMobiles[index].lon);
            var lonRounded=Math.round(boueesMobiles[index].lon * 1000000) / 1000000; // Arrondi à la 6 ème décimale pour l'affichage
            var latRounded=Math.round(boueesMobiles[index].lat * 1000000) / 1000000;
            tmarkersMobiles[index] = L.marker(latlng, {title: "Bouée N°"+boueesMobiles[index].id,clickable: true,draggable: false,icon: myIcon}).bindPopup(description + " ("+lonRounded+","+latRounded+")");                       
        }
    }     
    if (tmarkersMobiles.length>0)
    {
        mesMarkersMobiles=L.layerGroup(tmarkersMobiles);
    	setTimeout(() => {  
            // Ajout des boueesMobiles
            map.addLayer(mesMarkersMobiles);
            // Regénération du contrôle
            layerControl.remove();
            // Redéfinir l'overlay         
            overlayMaps = {
                "Info":baliseSite,
                "Zone Nav.":zonenav, 
                "Concurrents":zoneconcurrents, 
                "Bouées ancrées":balises,
                "Mobiles":mesMarkersMobiles
            }      
            
            layerControl = L.control.layers(baseMaps, overlayMaps,  { collapsed: true }).addTo(map);                     	    
        }, 500)
    }
}

        