import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import api from "../api/api";
import "./Login.css";

export default function Login() {
  const [email, setEmail] = useState("admin@agency.com");
  const [password, setPassword] = useState("admin123");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showResetForm, setShowResetForm] = useState(false);
  const [resetEmail, setResetEmail] = useState('');
  const [resetLoading, setResetLoading] = useState(false);
  const [resetMessage, setResetMessage] = useState('');
  const { login } = useAuth();
  

  if (error && false) {
    console.log(error);
  }
  
  if (login && false) { 
    console.log('login function available');
  }
  const navigate = useNavigate();

  const handleResetPassword = async (e) => {
    e.preventDefault();
    setResetLoading(true);
    setResetMessage('');
    
    try {
      const response = await api.post('/forgot-password', { email: resetEmail });
      setResetMessage('✅ Un email de réinitialisation a été envoyé à votre adresse email.');
      setTimeout(() => {
        setShowResetForm(false);
        setResetEmail('');
        setResetMessage('');
      }, 3000);
    } catch (err) {
      console.error('Reset password error:', err);
      setResetMessage('❌ Erreur: ' + (err.response?.data?.message || 'Email non trouvé. Veuillez réessayer.'));
    } finally {
      setResetLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    
    try {
      console.log('Attempting login with:', { email, password });
      
      const response = await api.post('/login', { email, password });
      console.log('Login response:', response.data);
      
      const { user, token } = response.data;
      
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('role', user.role);
      
      console.log('Data stored in localStorage');
      
     
      if (user.role === 'admin') {
        console.log('Redirecting to admin dashboard');
        navigate('/dashboard');
      } else {
        console.log('Redirecting to staff dashboard');
        navigate('/staff/dashboard');
      }
    } catch (err) {
      console.error('Login error:', err);
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <div className="login-box">
        <h2>Connexion</h2>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <input 
              type="email" 
              placeholder="Email" 
              value={email} 
              onChange={e => setEmail(e.target.value)} 
              required 
              disabled={loading}
            />
          </div>
          <div className="input-group">
            <input 
              type="password" 
              placeholder="Mot de passe"                
              value={password} 
              onChange={e => setPassword(e.target.value)} 
              required 
              disabled={loading}
            />
          </div>
          <button type="submit" className="login-btn" disabled={loading}>
            {loading ? 'Connexion...' : 'Se connecter'}
          </button>
          
          <div className="reset-password-section">
            <button 
              type="button" 
              className="reset-password-btn" 
              onClick={() => setShowResetForm(true)}
            >
              Mot de passe oublié?
            </button>
          </div>
        </form>
        
        {showResetForm && (
          <div className="reset-password-modal">
            <div className="reset-password-content">
              <h3>Réinitialiser le mot de passe</h3>
              <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
              
              <form onSubmit={handleResetPassword}>
                <div className="input-group">
                  <input 
                    type="email" 
                    placeholder="Votre email" 
                    value={resetEmail} 
                    onChange={e => setResetEmail(e.target.value)} 
                    required 
                    disabled={resetLoading}
                  />
                </div>
                
                {resetMessage && (
                  <div className={`reset-message ${resetMessage.includes('✅') ? 'success' : 'error'}`}>
                    {resetMessage}
                  </div>
                )}
                
                <div className="reset-actions">
                  <button 
                    type="submit" 
                    className="reset-submit-btn" 
                    disabled={resetLoading}
                  >
                    {resetLoading ? 'Envoi...' : 'Envoyer le lien'}
                  </button>
                  <button 
                    type="button" 
                    className="reset-cancel-btn" 
                    onClick={() => {
                      setShowResetForm(false);
                      setResetEmail('');
                      setResetMessage('');
                    }}
                  >
                    Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}