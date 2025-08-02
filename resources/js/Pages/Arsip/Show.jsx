import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'; // Gunakan layout yang sesuai
import { Head } from '@inertiajs/react';

// Komponen kecil untuk menampilkan baris detail
const DetailRow = ({ label, value, isFile = false }) => (
    <div className="grid grid-cols-3 gap-4 py-2 border-b">
        <dt className="font-medium text-gray-600">{label}</dt>
        <dd className="col-span-2 text-gray-800">
            {isFile && value ? (
                <a href={`/storage/${value}`} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                    Lihat Dokumen
                </a>
            ) : (
                value || '-'
            )}
        </dd>
    </div>
);

// Komponen untuk menampilkan bagian/section
const DetailSection = ({ title, children }) => (
    <div className="mb-8">
        <h3 className="text-xl font-semibold text-gray-800 border-b-2 border-blue-600 pb-2 mb-4">
            {title}
        </h3>
        <dl>{children}</dl>
    </div>
);

export default function Show({ auth, kegiatan }) {
    const { data } = kegiatan;

    // Pisahkan dokumentasi berdasarkan tipe untuk memudahkan rendering
    const dokObservasi = data.dokumentasi_kegiatans.find(d => d.tipe === 'observasi');
    const dokPenyerahan = data.dokumentasi_kegiatans.find(d => d.tipe === 'penyerahan');
    const beritaAcara = data.berita_acaras[0]; // Asumsi hanya ada satu

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detail Arsip Kegiatan: {data.nama_kegiatan}</h2>}
        >
            <Head title={"Detail Arsip " + data.nama_kegiatan} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 md:p-8 text-gray-900">

                            {/* --- Bagian Detail Utama Kegiatan --- */}
                            <DetailSection title="Informasi Umum Kegiatan">
                                <DetailRow label="Nama Kegiatan" value={data.nama_kegiatan} />
                                <DetailRow label="Deskripsi" value={data.deskripsi_kegiatan} />
                                <DetailRow label="Tanggal Kegiatan" value={data.tanggal_kegiatan} />
                                <DetailRow label="Tim Pelaksana" value={data.tim.nama_tim} />
                                <DetailRow label="Anggota Tim" value={data.tim.pegawai.map(p => p.name).join(', ')} />
                                <DetailRow label="File SKTL" value={data.file_sktl} isFile={true} />
                            </DetailSection>

                            {/* --- Bagian Detail Proposal --- */}
                            <DetailSection title="Detail Proposal Terkait">
                                <DetailRow label="Nama Proposal" value={data.proposal.nama_proposal} />
                                <DetailRow label="Pengusul" value={data.proposal.user.name} />
                                <DetailRow label="Tujuan" value={data.proposal.tujuan} />
                                <DetailRow label="Dokumen Proposal" value={data.proposal.dokumen_pendukung} isFile={true} />
                            </DetailSection>

                            {/* --- Bagian Dokumentasi Observasi --- */}
                            {dokObservasi && (
                                <DetailSection title="Dokumentasi Observasi">
                                    <DetailRow label="Catatan Kebutuhan" value={dokObservasi.catatan_kebutuhan} />
                                    <DetailRow label="Detail Pelaksanaan" value={dokObservasi.detail_pelaksanaan} />
                                    <DetailRow label="Diunggah oleh" value={dokObservasi.user.name} />
                                    <div className="py-2">
                                        <p className="font-medium text-gray-600 mb-2">Foto-foto Observasi:</p>
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            {dokObservasi.fotos.map(foto => (
                                                <a key={foto.id} href={`/storage/${foto.path}`} target="_blank" rel="noopener noreferrer">
                                                    <img src={`/storage/${foto.path}`} alt="Dokumentasi" className="rounded-lg shadow-md object-cover h-40 w-full"/>
                                                </a>
                                            ))}
                                        </div>
                                    </div>
                                </DetailSection>
                            )}

                             {/* --- Bagian Dokumentasi Penyerahan --- */}
                            {dokPenyerahan && (
                                <DetailSection title="Dokumentasi Penyerahan">
                                    <DetailRow label="Nama Dokumentasi" value={dokPenyerahan.nama_dokumentasi} />
                                    <DetailRow label="File SKTL Penyerahan" value={dokPenyerahan.file_sktl_penyerahan} isFile={true} />
                                    <DetailRow label="Kontrak Pihak Ketiga" value={dokPenyerahan.kontraks[0]?.path} isFile={true} />
                                    <DetailRow label="Diunggah oleh" value={dokPenyerahan.user.name} />
                                </DetailSection>
                            )}

                            {/* --- Bagian Laporan Akhir --- */}
                             {beritaAcara && (
                                <DetailSection title="Laporan Akhir (Penyelesaian)">
                                    <DetailRow label="Status Akhir" value={data.status_akhir} />
                                    <DetailRow label="Tanggal Penyelesaian" value={data.tanggal_penyelesaian} />
                                    <DetailRow label="Detail Akhir Kegiatan" value={data.detail_akhir_kegiatan} />
                                    <DetailRow label="File Berita Acara" value={beritaAcara.path} isFile={true} />
                                    <DetailRow label="Diunggah oleh" value={beritaAcara.user.name} />
                                </DetailSection>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
