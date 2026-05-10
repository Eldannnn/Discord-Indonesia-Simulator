import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata = {
  title: "Discord Indonesia Simulator — Simulasi Politik Nusantara",
  description: "Platform simulasi permainan peran politik Indonesia. Berkampanye, bernegosiasi di DPR, dan bentuk masa depan republik.",
};

export default function RootLayout({ children }) {
  return (
    <html lang="id">
      <body>{children}</body>
    </html>
  );
}
