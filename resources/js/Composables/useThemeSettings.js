import { ref, watch } from 'vue';

/**
 * Composable untuk mengatur tema aplikasi (dark/light mode)
 */
export function useThemeSettings() {
  // State untuk menyimpan status dark mode
  const isDarkMode = ref(false);
  
  // Deteksi preferensi warna sistem
  const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)');
  
  // Fungsi untuk menginisialisasi tema dari local storage atau preferensi sistem
  const initializeTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme) {
      // Gunakan tema yang disimpan sebelumnya
      isDarkMode.value = savedTheme === 'dark';
    } else {
      // Gunakan preferensi sistem
      isDarkMode.value = prefersDarkMode.matches;
    }
    
    // Terapkan tema
    applyTheme();
  };
  
  // Fungsi untuk mengganti tema
  const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
  };
  
  // Fungsi untuk menerapkan tema ke dokumen
  const applyTheme = () => {
    if (isDarkMode.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };
  
  // Watch perubahan tema dan aplikasikan
  watch(isDarkMode, () => {
    applyTheme();
  });
  
  // Listen untuk perubahan preferensi sistem
  prefersDarkMode.addEventListener('change', (e) => {
    // Hanya update tema jika tidak ada preferensi tersimpan
    if (!localStorage.getItem('theme')) {
      isDarkMode.value = e.matches;
    }
  });
  
  // Panggil inisialisasi tema
  initializeTheme();
  
  // Kembalikan state dan fungsi
  return {
    isDarkMode,
    toggleDarkMode
  };
} 