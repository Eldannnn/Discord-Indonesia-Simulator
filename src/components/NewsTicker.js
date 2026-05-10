export default function NewsTicker() {
  const news = [
    { id: 1, text: "Parlemen menyetujui RUU Anggaran 2027", time: "10:30" },
    { id: 2, text: "Kunjungan diplomatik ke negara tetangga berjalan lancar", time: "09:15" },
    { id: 3, text: "Indeks harga saham gabungan (IHSG) menguat 1.2%", time: "08:00" }
  ];

  return (
    <div className="glass-panel animate-fade-in delay-2" style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
      <h3 style={{ marginBottom: '1rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
        <span style={{ color: 'var(--primary-red)' }}>●</span> Live Update Parlemen
      </h3>
      <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        {news.map(item => (
          <div key={item.id} style={{ display: 'flex', gap: '1rem', borderBottom: '1px solid var(--surface-border)', paddingBottom: '0.75rem' }}>
            <span style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>{item.time}</span>
            <p style={{ fontSize: '0.9rem', lineHeight: '1.4' }}>{item.text}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
