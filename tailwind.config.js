export default {
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        scada: {
          "primary": "#FF8C42",      // Industrial orange
          "secondary": "#8A8D91",    // Gray
          "accent": "#FFA726",       // Amber accent
          "neutral": "#171A1F",      // Dark panel
          "base-100": "#0F1115",     // Darkest background
          "base-200": "#171A1F",     // Panel surface
          "base-300": "#1E2228",     // Elevated surface
          "info": "#42A5F5",         // System blue
          "success": "#66BB6A",      // Normal state
          "warning": "#FFA726",      // Warning state
          "error": "#EF5350",        // Critical state
          "base-content": "#E8E9EA", // Light text
        },
      },
    ],
  },
};
