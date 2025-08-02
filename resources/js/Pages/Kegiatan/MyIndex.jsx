import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function MyIndex({ auth, kegiatans, success }) {
    // Objek untuk memetakan nama teknis tahapan ke nama yang lebih ramah pengguna
    const tahapanFriendlyNames = {
        'Perjalanan Dinas': 'Melakukan Perjalanan Dinas Observasi',
        'Dokumentasi Observasi': 'Melakukan Dokumentasi Observasi',
        'Dokumentasi Penyerahan': 'Melakukan Dokumentasi Penyerahan',
        'Penyelesaian': 'Selesai',
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Daftar Kegiatan Saya</h2>}
        >
            <Head title="Kegiatan Saya" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success && (
                        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span className="block sm:inline">{success}</span>
                        </div>
                    )}

                    {/* Navigasi Tahapan (seperti di gambar referensi) */}
                    <div className="mb-4 flex space-x-2">
                        {Object.values(tahapanFriendlyNames).map((nama, index) => (
                            <button
                                key={index}
                                className={`px-4 py-2 text-sm font-medium rounded-md ${
                                    index === 0 // Anggap saja tahapan pertama selalu aktif untuk contoh ini
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                {nama}
                            </button>
                        ))}
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="overflow-auto">
                                <table className="w-full text-sm text-left rtl:text-right text-gray-500">
                                    <thead className="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr className="bg-blue-900 text-white">
                                            <th className="px-6 py-3">No</th>
                                            <th className="px-6 py-3">Nama Kegiatan</th>
                                            <th className="px-6 py-3">Proposal</th>
                                            <th className="px-6 py-3">Tanggal</th>
                                            <th className="px-6 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {kegiatans.data.map((kegiatan, index) => (
                                            <tr key={kegiatan.id} className="bg-white border-b">
                                                <td className="px-6 py-4">{index + 1}</td>
                                                <td className="px-6 py-4">{kegiatan.nama_kegiatan}</td>
                                                <td className="px-6 py-4">
                                                    <Link
                                                        href={route('proposal.show', { proposal: kegiatan.proposal.id })}
                                                        className="font-medium text-green-600 hover:underline"
                                                    >
                                                        Lihat
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4">{kegiatan.tanggal_kegiatan}</td>
                                                <td className="px-6 py-4 text-center">
                                                    <Link
                                                        href={route('kegiatan.show', { kegiatan: kegiatan.id })}
                                                        className="px-4 py-2 text-white bg-cyan-500 rounded-md hover:bg-cyan-600"
                                                    >
                                                        Konfirmasi & Detail
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
