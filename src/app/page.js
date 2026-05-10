'use client';

export default function Home() {
  return (
    <>
      <div className="page-texture" aria-hidden="true"></div>

      {/* Topbar */}
      <header className="topbar">
        <div className="topbar-brand">
          <div className="brand-mark">DIS</div>
          <div className="brand-text">Discord <span>Indonesia</span> Simulator</div>
        </div>
        <nav className="topbar-nav">
          {['Beranda', 'Karakter', 'Partai', 'Pemilu', 'DPR/MPR', 'Istana', 'Ekonomi', 'Kebijakan'].map((item, i) => (
            <a href="#" key={i} className={i === 0 ? 'active' : ''}>{item}</a>
          ))}
        </nav>
        <div className="topbar-right">
          <div className="status-badge">Online</div>
          <button className="btn-enter">Masuk Simulator</button>
        </div>
      </header>

      <main className="main-content">

        {/* Hero */}
        <section className="hero">
          <div className="hero-left">
            <div className="hero-tag">Simulasi Politik Interaktif</div>
            <h1 className="hero-title">
              Tulis sejarah<br />
              <em>Nusantara</em> baru.
            </h1>
            <p className="hero-desc">
              Masuki dunia simulasi politik Indonesia yang hidup. Bangun partai, 
              menangkan pemilu, pimpin kabinet, dan rancang undang-undang yang 
              mengubah arah bangsa — semua dalam satu platform.
            </p>
            <div className="hero-actions">
              <button className="cta-primary" onClick={() => alert('Selamat datang di Simulator!')}>
                Mulai Bermain <span className="arrow">&rarr;</span>
              </button>
              <button className="cta-ghost">Pelajari Lebih Lanjut</button>
            </div>
          </div>

          <div className="hero-right">
            <div className="info-panel ip-1">
              <div className="ip-header">
                <div className="ip-dot red"></div>
                <span className="ip-label">Istana Negara</span>
              </div>
              <p className="ip-body">Jabatan Presiden tersedia. 4 kandidat berkompetisi di putaran final.</p>
              <div className="ip-meter">
                <div className="ip-meter-fill red" style={{ width: '72%' }}></div>
              </div>
            </div>

            <div className="info-panel ip-2">
              <div className="ip-header">
                <div className="ip-dot gold"></div>
                <span className="ip-label">Sidang Paripurna</span>
              </div>
              <p className="ip-body">RUU Pendidikan Nasional sedang dibahas. 3 fraksi mengajukan amandemen.</p>
              <div className="ip-meter">
                <div className="ip-meter-fill gold" style={{ width: '45%' }}></div>
              </div>
            </div>

            <div className="info-panel ip-3">
              <div className="ip-header">
                <div className="ip-dot dark"></div>
                <span className="ip-label">APBN 2027</span>
              </div>
              <p className="ip-body">Defisit anggaran 2.8% dari PDB. Menteri Keuangan mengusulkan reformasi pajak.</p>
              <div className="ip-meter">
                <div className="ip-meter-fill red" style={{ width: '60%' }}></div>
              </div>
            </div>
          </div>
        </section>

        {/* Divider */}
        <div className="divider"></div>

        {/* Stats */}
        <section className="stats-row">
          <div className="stat-cell">
            <span className="stat-number accent">21</span>
            <span className="stat-desc">Pemain Aktif</span>
          </div>
          <div className="stat-cell">
            <span className="stat-number">486</span>
            <span className="stat-desc">Pemilu Berlangsung</span>
          </div>
          <div className="stat-cell">
            <span className="stat-number accent">3</span>
            <span className="stat-desc">RUU Diajukan</span>
          </div>
          <div className="stat-cell">
            <span className="stat-number">11</span>
            <span className="stat-desc">UU Disahkan</span>
          </div>
          <div className="stat-cell">
            <span className="stat-number accent">2</span>
            <span className="stat-desc">Partai Politik</span>
          </div>
        </section>

        {/* Divider */}
        <div className="divider"></div>

        {/* Features */}
        <section className="features-section">
          <article className="pillar">
            <span className="pillar-seq">01</span>
            <div className="pillar-icon">P</div>
            <h3 className="pillar-title">Rebut Kursi Presiden</h3>
            <p className="pillar-body">
              Bangun koalisi, rancang kampanye nasional, debat melawan rival, 
              dan pimpin Indonesia dari Istana Merdeka. Setiap keputusan Anda 
              membentuk nasib 270 juta rakyat virtual.
            </p>
            <a href="#" className="pillar-action">Mulai Kampanye <span>&rarr;</span></a>
          </article>

          <article className="pillar">
            <span className="pillar-seq">02</span>
            <div className="pillar-icon">D</div>
            <h3 className="pillar-title">Kuasai Parlemen</h3>
            <p className="pillar-body">
              Duduki kursi DPR atau DPD, bentuk fraksi, dan kendalikan arah 
              legislasi. Negosiasikan anggaran triliunan dan tentukan 
              siapa yang mendapat alokasi dana terbesar.
            </p>
            <a href="#" className="pillar-action">Ajukan Kandidat <span>&rarr;</span></a>
          </article>

          <article className="pillar">
            <span className="pillar-seq">03</span>
            <div className="pillar-icon">U</div>
            <h3 className="pillar-title">Rancang Undang-Undang</h3>
            <p className="pillar-body">
              Tulis RUU dari nol, lobby dukungan lintas fraksi, negosiasikan 
              pasal demi pasal, dan saksikan hukum buatan Anda mengubah 
              mekanisme seluruh simulasi.
            </p>
            <a href="#" className="pillar-action">Buka Editor RUU <span>&rarr;</span></a>
          </article>
        </section>

        {/* Footer */}
        <footer className="site-footer">
          <div className="footer-copy">
            &copy; 2026 <strong>Discord Indonesia Simulator</strong> — Simulasi Politik Nusantara
          </div>
          <div className="footer-links">
            <a href="#">Discord Server</a>
            <a href="#">Dokumentasi</a>
            <a href="#">Kontribusi</a>
          </div>
        </footer>
      </main>
    </>
  );
}
