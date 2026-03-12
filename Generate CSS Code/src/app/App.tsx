import { BookOpen, Home, Database, Laptop, BookMarked, UserCircle, FileText } from 'lucide-react';

export default function App() {
  return (
    <div className="min-h-screen bg-gray-100 flex">
      {/* Sidebar */}
      <aside className="w-72 bg-[#2c3e50] text-white flex flex-col">
        {/* Header */}
        <div className="p-6 border-b border-[#34495e]">
          <div className="flex items-center gap-3">
            <div className="bg-blue-500 p-2 rounded">
              <BookOpen className="size-6" />
            </div>
            <h1 className="text-xl font-semibold">Perpus Kita</h1>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 p-4">
          <ul className="space-y-2">
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded bg-[#34495e] text-white hover:bg-[#34495e] transition">
                <Home className="size-5" />
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded text-gray-300 hover:bg-[#34495e] transition">
                <Database className="size-5" />
                <span>Data Master</span>
              </a>
            </li>
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded text-gray-300 hover:bg-[#34495e] transition">
                <Laptop className="size-5" />
                <div>
                  <div>Data Rak</div>
                  <div className="text-sm text-gray-400">Pengarang & Penerbit</div>
                </div>
              </a>
            </li>
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded text-gray-300 hover:bg-[#34495e] transition">
                <BookMarked className="size-5" />
                <span>Katalog Buku</span>
              </a>
            </li>
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded text-gray-300 hover:bg-[#34495e] transition">
                <UserCircle className="size-5" />
                <span>Peminjaman</span>
              </a>
            </li>
            <li>
              <a href="#" className="flex items-center gap-3 px-4 py-3 rounded text-gray-300 hover:bg-[#34495e] transition">
                <FileText className="size-5" />
                <span>Laporan</span>
              </a>
            </li>
          </ul>
        </nav>
      </aside>

      {/* Main Content */}
      <main className="flex-1 flex flex-col">
        {/* Top Bar */}
        <header className="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
          <h2 className="text-2xl font-semibold text-gray-800">Selamat Datang, Admin!</h2>
          <button className="p-2 rounded-full bg-gray-200 hover:bg-gray-300">
            <UserCircle className="size-6 text-gray-600" />
          </button>
        </header>

        {/* Content Area */}
        <div className="flex-1 p-8">
          <p className="text-gray-500 mb-8">Selamat Datang, Admin!</p>

          {/* Stats Cards */}
          <div className="grid grid-cols-4 gap-6 mb-8">
            <div className="bg-blue-500 text-white rounded-lg p-6 shadow-md">
              <div className="text-sm mb-2">Total Buku</div>
              <div className="text-4xl font-bold">250</div>
            </div>
            <div className="bg-green-500 text-white rounded-lg p-6 shadow-md">
              <div className="text-sm mb-2">Anggota Terdaftar</div>
              <div className="text-4xl font-bold">120</div>
            </div>
            <div className="bg-yellow-500 text-white rounded-lg p-6 shadow-md">
              <div className="text-sm mb-2">Buku Dipinjam</div>
              <div className="text-4xl font-bold">35</div>
            </div>
            <div className="bg-red-500 text-white rounded-lg p-6 shadow-md">
              <div className="text-sm mb-2">Peminjan Terlambat</div>
              <div className="text-4xl font-bold">8</div>
            </div>
          </div>

          {/* Table Section */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h3 className="text-xl font-semibold text-gray-800 mb-4">Daftar Peminjaman Terbato</h3>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gray-200">
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">No.</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Judul Buku</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Tgl Pinjam</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Tgl Kembali</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 text-gray-500">1</td>
                    <td className="py-3 px-4 text-gray-700">Laskar Pelangi</td>
                    <td className="py-3 px-4 text-gray-700">01/02/2026</td>
                    <td className="py-3 px-4 text-gray-700">08/02/2026</td>
                    <td className="py-3 px-4">
                      <span className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Dipinjam</span>
                    </td>
                  </tr>
                  <tr className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 text-gray-500">2</td>
                    <td className="py-3 px-4 text-gray-700">Bumi Manusia</td>
                    <td className="py-3 px-4 text-gray-700">28/01/2026</td>
                    <td className="py-3 px-4 text-gray-700">05/02/2026</td>
                    <td className="py-3 px-4">
                      <span className="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Terlambat</span>
                    </td>
                  </tr>
                  <tr className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 text-gray-500">3</td>
                    <td className="py-3 px-4 text-gray-700">Perahu Kertas</td>
                    <td className="py-3 px-4 text-gray-700">03/02/2026</td>
                    <td className="py-3 px-4 text-gray-700">10/02/2026</td>
                    <td className="py-3 px-4">
                      <span className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Dipinjam</span>
                    </td>
                  </tr>
                  <tr className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 text-gray-500">4</td>
                    <td className="py-3 px-4 text-gray-700">5 cm</td>
                    <td className="py-3 px-4 text-gray-700">25/01/2026</td>
                    <td className="py-3 px-4 text-gray-700">01/02/2026</td>
                    <td className="py-3 px-4">
                      <span className="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Terlambat</span>
                    </td>
                  </tr>
                  <tr className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 text-gray-500">5</td>
                    <td className="py-3 px-4 text-gray-700">Dilan 1990</td>
                    <td className="py-3 px-4 text-gray-700">05/02/2026</td>
                    <td className="py-3 px-4 text-gray-700">12/02/2026</td>
                    <td className="py-3 px-4">
                      <span className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Dipinjam</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}