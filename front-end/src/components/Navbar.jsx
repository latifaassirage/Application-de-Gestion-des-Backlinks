import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import './Navbar.css';

const Navbar = () => {
  const { logout } = useAuth();
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  const isAdmin = user.role === 'admin';
  const [showDropdown, setShowDropdown] = useState(false);
  const [showMobileMenu, setShowMobileMenu] = useState(false);

  const handleLogout = () => {
    logout();
    window.location.href = '/login';
  };

  return (
    <nav className="navbar">
      <div className="navbar-brand">
        <span className="logo">B</span>
        <span className="brand-name">Agency SEO</span>
      </div>
      
      <button 
        className="mobile-menu-toggle"
        onClick={() => setShowMobileMenu(!showMobileMenu)}
      >
        <span className="hamburger-line"></span>
        <span className="hamburger-line"></span>
        <span className="hamburger-line"></span>
      </button>
      
      <div className={`navbar-links ${showMobileMenu ? 'mobile-open' : ''}`}>
        <Link to="/dashboard" onClick={() => setShowMobileMenu(false)}>Dashboard</Link>
        {isAdmin && <Link to="/clients" onClick={() => setShowMobileMenu(false)}>Clients</Link>}
        <Link to="/sources" onClick={() => setShowMobileMenu(false)}>Source Websites</Link>
        <Link to="/backlinks" onClick={() => setShowMobileMenu(false)}>Backlinks</Link>
        <Link to="/reports" onClick={() => setShowMobileMenu(false)}>Reports</Link>
      </div>
      
      <div className="navbar-user">
        <div className="user-dropdown">
          <button 
            className="user-info-btn" 
            onClick={() => setShowDropdown(!showDropdown)}
          >
            <span className="user-info">
              {user.name} ({user.role})
            </span>
            <span className="dropdown-arrow">▼</span>
          </button>
          
          {showDropdown && (
            <div className="dropdown-menu">
              <Link to="/profile" className="dropdown-item">
                👤 Profile
              </Link>
              <button className="dropdown-item logout" onClick={handleLogout}>
                🚪 Logout
              </button>
            </div>
          )}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
