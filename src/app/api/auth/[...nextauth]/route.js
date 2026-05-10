import NextAuth from "next-auth"
import DiscordProvider from "next-auth/providers/discord"

const handler = NextAuth({
  providers: [
    DiscordProvider({
      clientId: process.env.DISCORD_CLIENT_ID,
      clientSecret: process.env.DISCORD_CLIENT_SECRET,
      // Jika butuh mengakses server yang diikuti user, Anda bisa tambah parameter authorization:
      // authorization: { params: { scope: 'identify email guilds' } },
    }),
  ],
  callbacks: {
    async session({ session, token, user }) {
      // Kirim data user id ke dalam session agar bisa dipakai di aplikasi
      if (session.user) {
        session.user.id = token.sub;
      }
      return session;
    },
  },
  secret: process.env.NEXTAUTH_SECRET,
});

export { handler as GET, handler as POST }
