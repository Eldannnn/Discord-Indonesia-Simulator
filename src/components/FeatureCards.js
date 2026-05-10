export default function FeatureCards() {
  const features = [
    {
      title: "Menjadi Presiden",
      desc: "Bangun kampanye nasional, menangkan pemilihan umum, dan tanda tangani rancangan undang-undang dari Ruang Kerja Istana Negara."
    },
    {
      title: "Bergabung dengan DPR",
      desc: "Mencalonkan diri untuk kursi DPR atau DPD, pimpin kaukus Anda, dan dorong RUU melalui pemungutan suara bersejarah di parlemen."
    },
    {
      title: "Tulis RUU Bersejarah",
      desc: "Rancang undang-undang, negosiasikan amandemen, dan sahkan proposal yang dapat mengubah arah bangsa."
    }
  ];

  return (
    <div className="features-container">
      {features.map((feature, index) => (
        <div key={index} className="card feature-card">
          <h3>{feature.title}</h3>
          <p>{feature.desc}</p>
        </div>
      ))}
    </div>
  );
}
