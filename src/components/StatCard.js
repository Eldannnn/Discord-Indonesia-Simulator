export default function StatCard({ title, value, trend, icon, delay = '' }) {
  const isPositive = trend > 0;
  return (
    <div className={`glass-panel stat-card animate-fade-in ${delay}`} style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <span style={{ color: 'var(--text-muted)', fontSize: '0.9rem', fontWeight: '500' }}>{title}</span>
        <span style={{ fontSize: '1.2rem' }}>{icon}</span>
      </div>
      <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>{value}</div>
      <div style={{ fontSize: '0.8rem', color: isPositive ? 'var(--accent-green)' : 'var(--primary-red)' }}>
        {isPositive ? '↑' : '↓'} {Math.abs(trend)}% dari periode lalu
      </div>
    </div>
  );
}
