export default function Navbar() {
  const links = [
    { name: 'Beranda', active: false },
    { name: 'Karakter', active: false },
    { name: 'Ekonomi', active: false },
    { name: 'Partai', active: false },
    { name: 'Pemilu', active: false },
    { name: 'DPR', active: false },
    { name: 'Istana Negara', active: false },
    { name: 'Direktori', active: false },
    { name: 'Kebijakan', active: false },
    { name: 'Kabinet', active: true },
  ];

  return (
    <nav className="navbar">
      <div className="container nav-content">
        <div className="nav-brand">Discord Indonesia Simulator</div>
        <div className="nav-links">
          {links.map((link, index) => (
            <a href="#" key={index} className={`nav-link ${link.active ? 'active' : ''}`}>
              {link.name}
            </a>
          ))}
        </div>
        <div className="nav-right">
          <span>Session active</span>
          <a href="#" style={{ fontWeight: '600', color: 'var(--foreground)' }}>SIGN OUT</a>
        </div>
      </div>
    </nav>
  );
}
