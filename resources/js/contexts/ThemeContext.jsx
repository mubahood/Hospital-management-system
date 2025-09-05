import React, { createContext, useContext, useState, useEffect } from 'react';

const ThemeContext = createContext();

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

const themes = {
  primary: {
    name: 'Primary Blue',
    value: 'primary',
    color: 'rgb(59, 130, 246)',
  },
  green: {
    name: 'Medical Green',
    value: 'green',
    color: 'rgb(34, 197, 94)',
  },
  purple: {
    name: 'Royal Purple',
    value: 'purple',
    color: 'rgb(147, 51, 234)',
  },
  pink: {
    name: 'Hospital Pink',
    value: 'pink',
    color: 'rgb(236, 72, 153)',
  },
  orange: {
    name: 'Warm Orange',
    value: 'orange',
    color: 'rgb(249, 115, 22)',
  },
};

export default function ThemeProvider({ children }) {
  const [currentTheme, setCurrentTheme] = useState(() => {
    return localStorage.getItem('app_theme') || 'primary';
  });

  useEffect(() => {
    document.documentElement.setAttribute('data-theme', currentTheme);
    localStorage.setItem('app_theme', currentTheme);
  }, [currentTheme]);

  const changeTheme = (themeName) => {
    if (themes[themeName]) {
      setCurrentTheme(themeName);
    }
  };

  const value = {
    currentTheme,
    changeTheme,
    themes,
    getCurrentThemeData: () => themes[currentTheme],
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
}
