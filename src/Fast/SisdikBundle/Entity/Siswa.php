<?php

namespace Fast\SisdikBundle\Entity;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Fast\SisdikBundle\Util\FileSizeFormatter;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Siswa
 *
 * @ORM\Table(name="siswa", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="nomor_pendaftaran_UNIQUE", columns={"tahun_id", "nomor_pendaftaran"}),
 *     @ORM\UniqueConstraint(name="nomor_urut_pendaftaran_UNIQUE", columns={"tahun_id", "nomor_urut_pendaftaran"}),
 *     @ORM\UniqueConstraint(name="nomor_urut_persekolah_UNIQUE", columns={"sekolah_id", "nomor_urut_persekolah"}),
 *     @ORM\UniqueConstraint(name="nomor_induk_sistem_UNIQUE", columns={"nomor_induk_sistem"})
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Siswa
{
    const WEBCAMPHOTO_DIR = 'uploads/students/webcam-photos/';
    const PHOTO_DIR = 'uploads/students/photos/';
    const THUMBNAIL_PREFIX = 'th1-';
    const MEMORY_LIMIT = '256M';
    const PHOTO_THUMB_WIDTH = 80;
    const PHOTO_THUMB_HEIGHT = 150;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="nomor_urut_pendaftaran", type="smallint", nullable=true, options={"unsigned"=true})
     */
    private $nomorUrutPendaftaran;

    /**
     * @var string
     *
     * @ORM\Column(name="nomor_pendaftaran", type="string", length=45, nullable=true)
     */
    private $nomorPendaftaran;

    /**
     * @var boolean
     *
     * @ORM\Column(name="calon_siswa", type="boolean", nullable=false, options={"default"=0})
     */
    private $calonSiswa = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="nomor_urut_persekolah", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $nomorUrutPersekolah;

    /**
     * @var string
     *
     * @ORM\Column(name="nomor_induk_sistem", type="string", length=45, nullable=true)
     */
    private $nomorIndukSistem;

    /**
     * @var string
     *
     * @ORM\Column(name="nomor_induk", type="string", length=100, nullable=true)
     */
    private $nomorInduk;

    /**
     * @var string
     *
     * @ORM\Column(name="nama_lengkap", type="string", length=300, nullable=true)
     * @Assert\NotBlank
     */
    private $namaLengkap;

    /**
     * @var string
     *
     * @ORM\Column(name="jenis_kelamin", type="string", length=100, nullable=true)
     */
    private $jenisKelamin;

    /**
     * @var string
     *
     * @ORM\Column(name="foto_pendaftaran", type="string", length=100, nullable=true)
     */
    private $fotoPendaftaran;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="5M")
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=100, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="foto_disk", type="string", length=100, nullable=true)
     */
    private $fotoDisk;

    /**
     * @var string
     */
    private $fotoDiskSebelumnya;

    /**
     * @var string
     *
     * @ORM\Column(name="agama", type="string", length=100, nullable=true)
     */
    private $agama;

    /**
     * @var string
     *
     * @ORM\Column(name="tempat_lahir", type="string", length=400, nullable=true)
     */
    private $tempatLahir;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tanggal_lahir", type="date", nullable=true)
     */
    private $tanggalLahir;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="nama_panggilan", type="string", length=100, nullable=true)
     */
    private $namaPanggilan;

    /**
     * @var string
     *
     * @ORM\Column(name="kewarganegaraan", type="string", length=200, nullable=true)
     */
    private $kewarganegaraan;

    /**
     * @var integer
     *
     * @ORM\Column(name="anak_ke", type="smallint", nullable=true)
     */
    private $anakKe;

    /**
     * @var integer
     *
     * @ORM\Column(name="jumlah_saudarakandung", type="smallint", nullable=true)
     */
    private $jumlahSaudarakandung;

    /**
     * @var integer
     *
     * @ORM\Column(name="jumlah_saudaratiri", type="smallint", nullable=true)
     */
    private $jumlahSaudaratiri;

    /**
     * @var string
     *
     * @ORM\Column(name="status_orphan", type="string", length=100, nullable=true)
     */
    private $statusOrphan;

    /**
     * @var string
     *
     * @ORM\Column(name="bahasa_seharihari", type="string", length=200, nullable=true)
     */
    private $bahasaSeharihari;

    /**
     * @var string
     *
     * @ORM\Column(name="alamat", type="string", length=500, nullable=true)
     */
    private $alamat;

    /**
     * @var string
     *
     * @ORM\Column(name="kodepos", type="string", length=30, nullable=true)
     */
    private $kodepos;

    /**
     * @var string
     *
     * @ORM\Column(name="telepon", type="string", length=100, nullable=true)
     */
    private $telepon;

    /**
     * @var string
     *
     * @ORM\Column(name="ponsel_siswa", type="string", length=100, nullable=true)
     */
    private $ponselSiswa;

    /**
     * @var string
     *
     * @ORM\Column(name="sekolah_tinggaldi", type="string", length=400, nullable=true)
     */
    private $sekolahTinggaldi;

    /**
     * @var string
     *
     * @ORM\Column(name="jarak_tempat", type="string", length=300, nullable=true)
     */
    private $jarakTempat;

    /**
     * @var string
     *
     * @ORM\Column(name="cara_kesekolah", type="string", length=300, nullable=true)
     */
    private $caraKesekolah;

    /**
     * @var integer
     *
     * @ORM\Column(name="beratbadan", type="smallint", nullable=true)
     */
    private $beratbadan;

    /**
     * @var integer
     *
     * @ORM\Column(name="tinggibadan", type="smallint", nullable=true)
     */
    private $tinggibadan;

    /**
     * @var string
     *
     * @ORM\Column(name="golongandarah", type="string", length=50, nullable=true)
     */
    private $golongandarah;

    /**
     * @var boolean
     *
     * @ORM\Column(name="lunas_biaya_pendaftaran", type="boolean", nullable=false, options={"default" = 0})
     */
    private $lunasBiayaPendaftaran = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="sisa_biaya_pendaftaran", type="bigint", nullable=false, options={"default" = -999})
     */
    private $sisaBiayaPendaftaran = -999;

    /**
     * @var string
     *
     * @ORM\Column(name="keterangan", type="text", nullable=true)
     */
    private $keterangan;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="waktu_simpan", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $waktuSimpan;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="waktu_ubah", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $waktuUbah;

    /**
     * @var \Gelombang
     *
     * @ORM\ManyToOne(targetEntity="Gelombang")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gelombang_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $gelombang;

    /**
     * @var \Tahun
     *
     * @ORM\ManyToOne(targetEntity="Tahun")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tahun_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tahun;

    /**
     * @var \Sekolah
     *
     * @ORM\ManyToOne(targetEntity="Sekolah")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sekolah_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $sekolah;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dibuat_oleh_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $dibuatOleh;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="diubah_oleh_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $diubahOleh;

    /**
     * @var boolean
     */
    protected $adaReferensi;

    /**
     * @var string
     */
    private $namaReferensi;

    /**
     * @var \Referensi
     *
     * @ORM\ManyToOne(targetEntity="Referensi", inversedBy="siswa", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referensi_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $referensi;

    /**
     * @var string
     */
    private $namaSekolahAsal;

    /**
     * @var \SekolahAsal
     *
     * @ORM\ManyToOne(targetEntity="SekolahAsal", inversedBy="siswa", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sekolah_asal_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $sekolahAsal;

    /**
     * @var \OrangtuaWali
     *
     * @ORM\OneToMany(targetEntity="OrangtuaWali", mappedBy="siswa", cascade={"persist", "remove"})
     * @ORM\OrderBy({"aktif" = "DESC"})
     */
    private $orangtuaWali;

    /**
     * @var \DokumenSiswa
     *
     * @ORM\OneToMany(targetEntity="DokumenSiswa", mappedBy="siswa", cascade={"persist", "remove"})
     */
    private $dokumenSiswa;

    /**
     * @var \PendidikanSiswa
     *
     * @ORM\OneToMany(targetEntity="PendidikanSiswa", mappedBy="siswa", cascade={"persist", "remove"})
     */
    private $pendidikanSiswa;

    /**
     * @var \PenyakitSiswa
     *
     * @ORM\OneToMany(targetEntity="PenyakitSiswa", mappedBy="siswa", cascade={"persist", "remove"})
     */
    private $penyakitSiswa;

    /**
     * @var \PembayaranPendaftaran
     *
     * @ORM\OneToMany(targetEntity="PembayaranPendaftaran", mappedBy="siswa")
     */
    private $pembayaranPendaftaran;

    /**
     * @var \PembayaranSekali
     *
     * @ORM\OneToMany(targetEntity="PembayaranSekali", mappedBy="siswa")
     */
    private $pembayaranSekali;

    /**
     * @var \PembayaranRutin
     *
     * @ORM\OneToMany(targetEntity="PembayaranRutin", mappedBy="siswa")
     */
    private $pembayaranRutin;

    /**
     * @var \SiswaKelas
     *
     * @ORM\OneToMany(targetEntity="SiswaKelas", mappedBy="siswa")
     */
    private $siswaKelas;

    /**
     * constructor
     *
     */
    public function __construct() {
        $this->orangtuaWali = new ArrayCollection();
        $this->pembayaranPendaftaran = new ArrayCollection();
        $this->pembayaranSekali = new ArrayCollection();
        $this->pembayaranRutin = new ArrayCollection();
        $this->siswaKelas = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nomorUrutPendaftaran
     *
     * @param integer $nomorPendaftaran
     * @return Siswa
     */
    public function setNomorUrutPendaftaran($nomorUrutPendaftaran) {
        $this->nomorUrutPendaftaran = $nomorUrutPendaftaran;

        return $this;
    }

    /**
     * Get nomorUrutPendaftaran
     *
     * @return integer
     */
    public function getNomorUrutPendaftaran() {
        return $this->nomorUrutPendaftaran;
    }

    /**
     * Set nomorPendaftaran
     *
     * @param string $nomorPendaftaran
     * @return Siswa
     */
    public function setNomorPendaftaran($nomorPendaftaran) {
        $this->nomorPendaftaran = $nomorPendaftaran;

        return $this;
    }

    /**
     * Get nomorPendaftaran
     *
     * @return string
     */
    public function getNomorPendaftaran() {
        return $this->nomorPendaftaran;
    }

    /**
     * Set calonSiswa
     *
     * @param boolean $calonSiswa
     * @return Siswa
     */
    public function setCalonSiswa($calonSiswa) {
        $this->calonSiswa = $calonSiswa;

        return $this;
    }

    /**
     * Get calonSiswa
     *
     * @return boolean
     */
    public function isCalonSiswa() {
        return $this->calonSiswa;
    }

    /**
     * Set nomorUrutPersekolah
     *
     * @param integer $nomorUrutPersekolah
     * @return Siswa
     */
    public function setNomorUrutPersekolah($nomorUrutPersekolah) {
        $this->nomorUrutPersekolah = $nomorUrutPersekolah;

        return $this;
    }

    /**
     * Get nomorUrutPersekolah
     *
     * @return integer
     */
    public function getNomorUrutPersekolah() {
        return $this->nomorUrutPersekolah;
    }

    /**
     * Set nomorIndukSistem
     *
     * @param string $nomorIndukSistem
     * @return Siswa
     */
    public function setNomorIndukSistem($nomorIndukSistem) {
        $this->nomorIndukSistem = $nomorIndukSistem;

        return $this;
    }

    /**
     * Get nomorIndukSistem
     *
     * @return string
     */
    public function getNomorIndukSistem() {
        return $this->nomorIndukSistem;
    }

    /**
     * Set nomorInduk
     *
     * @param string $nomorInduk
     * @return Siswa
     */
    public function setNomorInduk($nomorInduk) {
        $this->nomorInduk = $nomorInduk;

        return $this;
    }

    /**
     * Get nomorInduk
     *
     * @return string
     */
    public function getNomorInduk() {
        return $this->nomorInduk;
    }

    /**
     * Set namaLengkap
     *
     * @param string $namaLengkap
     * @return Siswa
     */
    public function setNamaLengkap($namaLengkap) {
        $this->namaLengkap = $namaLengkap;

        return $this;
    }

    /**
     * Get namaLengkap
     *
     * @return string
     */
    public function getNamaLengkap() {
        return $this->namaLengkap;
    }

    /**
     * Set jenisKelamin
     *
     * @param string $jenisKelamin
     * @return Siswa
     */
    public function setJenisKelamin($jenisKelamin) {
        $this->jenisKelamin = $jenisKelamin;

        return $this;
    }

    /**
     * Get jenisKelamin
     *
     * @return string
     */
    public function getJenisKelamin() {
        return $this->jenisKelamin;
    }

    /**
     * Set fotoPendaftaran
     *
     * @param string $fotoPendaftaran
     * @return Siswa
     */
    public function setFotoPendaftaran($fotoPendaftaran) {
        $this->fotoPendaftaran = $fotoPendaftaran;

        return $this;
    }

    /**
     * Get fotoPendaftaran
     *
     * @return string
     */
    public function getFotoPendaftaran() {
        return $this->fotoPendaftaran;
    }

    /**
     * Set foto
     *
     * @param string $foto
     * @return Siswa
     */
    public function setFoto($foto) {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return string
     */
    public function getFoto() {
        return $this->foto;
    }

    /**
     * Set fotoDisk
     *
     * @param string $fotoDisk
     * @return DokumenSiswa
     */
    public function setFotoDisk($fotoDisk) {
        $this->fotoDisk = $fotoDisk;

        return $this;
    }

    /**
     * Get fotoDisk
     *
     * @return string
     */
    public function getFotoDisk() {
        return $this->fotoDisk;
    }

    /**
     * Set agama
     *
     * @param string $agama
     * @return Siswa
     */
    public function setAgama($agama) {
        $this->agama = $agama;

        return $this;
    }

    /**
     * Get agama
     *
     * @return string
     */
    public function getAgama() {
        return $this->agama;
    }

    /**
     * Set tempatLahir
     *
     * @param string $tempatLahir
     * @return Siswa
     */
    public function setTempatLahir($tempatLahir) {
        $this->tempatLahir = $tempatLahir;

        return $this;
    }

    /**
     * Get tempatLahir
     *
     * @return string
     */
    public function getTempatLahir() {
        return $this->tempatLahir;
    }

    /**
     * Set tanggalLahir
     *
     * @param \DateTime $tanggalLahir
     * @return Siswa
     */
    public function setTanggalLahir($tanggalLahir) {
        $this->tanggalLahir = $tanggalLahir;

        return $this;
    }

    /**
     * Get tanggalLahir
     *
     * @return \DateTime
     */
    public function getTanggalLahir() {
        return $this->tanggalLahir;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Siswa
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set namaPanggilan
     *
     * @param string $namaPanggilan
     * @return Siswa
     */
    public function setNamaPanggilan($namaPanggilan) {
        $this->namaPanggilan = $namaPanggilan;

        return $this;
    }

    /**
     * Get namaPanggilan
     *
     * @return string
     */
    public function getNamaPanggilan() {
        return $this->namaPanggilan;
    }

    /**
     * Set kewarganegaraan
     *
     * @param string $kewarganegaraan
     * @return Siswa
     */
    public function setKewarganegaraan($kewarganegaraan) {
        $this->kewarganegaraan = $kewarganegaraan;

        return $this;
    }

    /**
     * Get kewarganegaraan
     *
     * @return string
     */
    public function getKewarganegaraan() {
        return $this->kewarganegaraan;
    }

    /**
     * Set anakKe
     *
     * @param integer $anakKe
     * @return Siswa
     */
    public function setAnakKe($anakKe) {
        $this->anakKe = $anakKe;

        return $this;
    }

    /**
     * Get anakKe
     *
     * @return integer
     */
    public function getAnakKe() {
        return $this->anakKe;
    }

    /**
     * Set jumlahSaudarakandung
     *
     * @param integer $jumlahSaudarakandung
     * @return Siswa
     */
    public function setJumlahSaudarakandung($jumlahSaudarakandung) {
        $this->jumlahSaudarakandung = $jumlahSaudarakandung;

        return $this;
    }

    /**
     * Get jumlahSaudarakandung
     *
     * @return integer
     */
    public function getJumlahSaudarakandung() {
        return $this->jumlahSaudarakandung;
    }

    /**
     * Set jumlahSaudaratiri
     *
     * @param integer $jumlahSaudaratiri
     * @return Siswa
     */
    public function setJumlahSaudaratiri($jumlahSaudaratiri) {
        $this->jumlahSaudaratiri = $jumlahSaudaratiri;

        return $this;
    }

    /**
     * Get jumlahSaudaratiri
     *
     * @return integer
     */
    public function getJumlahSaudaratiri() {
        return $this->jumlahSaudaratiri;
    }

    /**
     * Set statusOrphan
     *
     * @param string $statusOrphan
     * @return Siswa
     */
    public function setStatusOrphan($statusOrphan) {
        $this->statusOrphan = $statusOrphan;

        return $this;
    }

    /**
     * Get statusOrphan
     *
     * @return string
     */
    public function getStatusOrphan() {
        return $this->statusOrphan;
    }

    /**
     * Set bahasaSeharihari
     *
     * @param string $bahasaSeharihari
     * @return Siswa
     */
    public function setBahasaSeharihari($bahasaSeharihari) {
        $this->bahasaSeharihari = $bahasaSeharihari;

        return $this;
    }

    /**
     * Get bahasaSeharihari
     *
     * @return string
     */
    public function getBahasaSeharihari() {
        return $this->bahasaSeharihari;
    }

    /**
     * Set alamat
     *
     * @param string $alamat
     * @return Siswa
     */
    public function setAlamat($alamat) {
        $this->alamat = $alamat;

        return $this;
    }

    /**
     * Get alamat
     *
     * @return string
     */
    public function getAlamat() {
        return $this->alamat;
    }

    /**
     * Set kodepos
     *
     * @param string $kodepos
     * @return Siswa
     */
    public function setKodepos($kodepos) {
        $this->kodepos = $kodepos;

        return $this;
    }

    /**
     * Get kodepos
     *
     * @return string
     */
    public function getKodepos() {
        return $this->kodepos;
    }

    /**
     * Set telepon
     *
     * @param string $telepon
     * @return Siswa
     */
    public function setTelepon($telepon) {
        $this->telepon = $telepon;

        return $this;
    }

    /**
     * Get telepon
     *
     * @return string
     */
    public function getTelepon() {
        return $this->telepon;
    }

    /**
     * Set ponselSiswa
     *
     * @param string $ponselSiswa
     * @return Siswa
     */
    public function setPonselSiswa($ponselSiswa) {
        $this->ponselSiswa = $ponselSiswa;

        return $this;
    }

    /**
     * Get ponselSiswa
     *
     * @return string
     */
    public function getPonselSiswa() {
        return $this->ponselSiswa;
    }

    /**
     * Set sekolahTinggaldi
     *
     * @param string $sekolahTinggaldi
     * @return Siswa
     */
    public function setSekolahTinggaldi($sekolahTinggaldi) {
        $this->sekolahTinggaldi = $sekolahTinggaldi;

        return $this;
    }

    /**
     * Get sekolahTinggaldi
     *
     * @return string
     */
    public function getSekolahTinggaldi() {
        return $this->sekolahTinggaldi;
    }

    /**
     * Set jarakTempat
     *
     * @param string $jarakTempat
     * @return Siswa
     */
    public function setJarakTempat($jarakTempat) {
        $this->jarakTempat = $jarakTempat;

        return $this;
    }

    /**
     * Get jarakTempat
     *
     * @return string
     */
    public function getJarakTempat() {
        return $this->jarakTempat;
    }

    /**
     * Set caraKesekolah
     *
     * @param string $caraKesekolah
     * @return Siswa
     */
    public function setCaraKesekolah($caraKesekolah) {
        $this->caraKesekolah = $caraKesekolah;

        return $this;
    }

    /**
     * Get caraKesekolah
     *
     * @return string
     */
    public function getCaraKesekolah() {
        return $this->caraKesekolah;
    }

    /**
     * Set beratbadan
     *
     * @param integer $beratbadan
     * @return Siswa
     */
    public function setBeratbadan($beratbadan) {
        $this->beratbadan = $beratbadan;

        return $this;
    }

    /**
     * Get beratbadan
     *
     * @return integer
     */
    public function getBeratbadan() {
        return $this->beratbadan;
    }

    /**
     * Set tinggibadan
     *
     * @param integer $tinggibadan
     * @return Siswa
     */
    public function setTinggibadan($tinggibadan) {
        $this->tinggibadan = $tinggibadan;

        return $this;
    }

    /**
     * Get tinggibadan
     *
     * @return integer
     */
    public function getTinggibadan() {
        return $this->tinggibadan;
    }

    /**
     * Set golongandarah
     *
     * @param string $golongandarah
     * @return Siswa
     */
    public function setGolongandarah($golongandarah) {
        $this->golongandarah = $golongandarah;

        return $this;
    }

    /**
     * Get golongandarah
     *
     * @return string
     */
    public function getGolongandarah() {
        return $this->golongandarah;
    }

    /**
     * Set lunasBiayaPendaftaran
     *
     * @param boolean $lunasBiayaPendaftaran
     * @return Siswa
     */
    public function setLunasBiayaPendaftaran($lunasBiayaPendaftaran) {
        $this->lunasBiayaPendaftaran = $lunasBiayaPendaftaran;

        return $this;
    }

    /**
     * Get lunasBiayaPendaftaran
     *
     * @return boolean
     */
    public function getLunasBiayaPendaftaran() {
        return $this->lunasBiayaPendaftaran;
    }

    /**
     * Set sisaBiayaPendaftaran
     *
     * @param boolean $sisaBiayaPendaftaran
     * @return Siswa
     */
    public function setSisaBiayaPendaftaran($sisaBiayaPendaftaran) {
        $this->sisaBiayaPendaftaran = $sisaBiayaPendaftaran;

        return $this;
    }

    /**
     * Get sisaBiayaPendaftaran
     *
     * @return integer
     */
    public function getSisaBiayaPendaftaran() {
        return $this->sisaBiayaPendaftaran;
    }

    /**
     * Set waktuSimpan
     *
     * @param \DateTime $waktuSimpan
     * @return Siswa
     */
    public function setWaktuSimpan($waktuSimpan) {
        $this->waktuSimpan = $waktuSimpan;

        return $this;
    }

    /**
     * Get waktuSimpan
     *
     * @return \DateTime
     */
    public function getWaktuSimpan() {
        return $this->waktuSimpan;
    }

    /**
     * Set waktuUbah
     *
     * @param \DateTime $waktuUbah
     * @return Siswa
     */
    public function setWaktuUbah($waktuUbah) {
        $this->waktuUbah = $waktuUbah;

        return $this;
    }

    /**
     * Get waktuUbah
     *
     * @return \DateTime
     */
    public function getWaktuUbah() {
        return $this->waktuUbah;
    }

    /**
     * Set keterangan
     *
     * @param string $keterangan
     * @return Siswa
     */
    public function setKeterangan($keterangan) {
        $this->keterangan = $keterangan;

        return $this;
    }

    /**
     * Get keterangan
     *
     * @return string
     */
    public function getKeterangan() {
        return $this->keterangan;
    }

    /**
     * Set gelombang
     *
     * @param \Fast\SisdikBundle\Entity\Gelombang $gelombang
     * @return Siswa
     */
    public function setGelombang(\Fast\SisdikBundle\Entity\Gelombang $gelombang = null) {
        $this->gelombang = $gelombang;

        return $this;
    }

    /**
     * Get gelombang
     *
     * @return \Fast\SisdikBundle\Entity\Gelombang
     */
    public function getGelombang() {
        return $this->gelombang;
    }

    /**
     * Set tahun
     *
     * @param \Fast\SisdikBundle\Entity\Tahun $tahun
     * @return Siswa
     */
    public function setTahun(\Fast\SisdikBundle\Entity\Tahun $tahun = null) {
        $this->tahun = $tahun;

        return $this;
    }

    /**
     * Get tahun
     *
     * @return \Fast\SisdikBundle\Entity\Tahun
     */
    public function getTahun() {
        return $this->tahun;
    }

    /**
     * Set sekolah
     *
     * @param \Fast\SisdikBundle\Entity\Sekolah $sekolah
     * @return Siswa
     */
    public function setSekolah(\Fast\SisdikBundle\Entity\Sekolah $sekolah = null) {
        $this->sekolah = $sekolah;

        return $this;
    }

    /**
     * Get sekolah
     *
     * @return \Fast\SisdikBundle\Entity\Sekolah
     */
    public function getSekolah() {
        return $this->sekolah;
    }

    /**
     * Set dibuatOleh
     *
     * @param \Fast\SisdikBundle\Entity\User $dibuatOleh
     * @return Siswa
     */
    public function setDibuatOleh(\Fast\SisdikBundle\Entity\User $dibuatOleh = null) {
        $this->dibuatOleh = $dibuatOleh;

        return $this;
    }

    /**
     * Get dibuatOleh
     *
     * @return \Fast\SisdikBundle\Entity\User
     */
    public function getDibuatOleh() {
        return $this->dibuatOleh;
    }

    /**
     * Set diubahOleh
     *
     * @param \Fast\SisdikBundle\Entity\User $diubahOleh
     * @return Siswa
     */
    public function setDiubahOleh(\Fast\SisdikBundle\Entity\User $diubahOleh = null) {
        $this->diubahOleh = $diubahOleh;

        return $this;
    }

    /**
     * Get diubahOleh
     *
     * @return \Fast\SisdikBundle\Entity\User
     */
    public function getDiubahOleh() {
        return $this->diubahOleh;
    }

    /**
     * Set adaReferensi
     *
     * @param boolean $adaReferensi
     * @return Siswa
     */
    public function setAdaReferensi($adaReferensi) {
        $this->adaReferensi = $adaReferensi;

        return $this;
    }

    /**
     * Is adaReferensi
     *
     * @return boolean
     */
    public function isAdaReferensi() {
        return $this->adaReferensi;
    }

    /**
     * Set namaReferensi
     *
     * @param string $namaReferensi
     * @return Siswa
     */
    public function setNamaReferensi($namaReferensi) {
        $this->namaReferensi = $namaReferensi;

        return $this;
    }

    /**
     * Get namaReferensi
     *
     * @return string
     */
    public function getNamaReferensi() {
        return $this->namaReferensi;
    }

    /**
     * Set referensi
     *
     * @param \Fast\SisdikBundle\Entity\Referensi $referensi
     * @return Siswa
     */
    public function setReferensi(\Fast\SisdikBundle\Entity\Referensi $referensi = null) {
        $this->referensi = $referensi;

        return $this;
    }

    /**
     * Get referensi
     *
     * @return \Fast\SisdikBundle\Entity\Referensi
     */
    public function getReferensi() {
        return $this->referensi;
    }

    /**
     * Set namaSekolahAsal
     *
     * @param string $namaSekolahAsal
     * @return Siswa
     */
    public function setNamaSekolahAsal($namaSekolahAsal) {
        $this->namaSekolahAsal = $namaSekolahAsal;

        return $this;
    }

    /**
     * Get namaSekolahAsal
     *
     * @return string
     */
    public function getNamaSekolahAsal() {
        return $this->namaSekolahAsal;
    }

    /**
     * Set sekolahAsal
     *
     * @param \Fast\SisdikBundle\Entity\SekolahAsal $sekolahAsal
     * @return Siswa
     */
    public function setSekolahAsal(\Fast\SisdikBundle\Entity\SekolahAsal $sekolahAsal = null) {
        $this->sekolahAsal = $sekolahAsal;

        return $this;
    }

    /**
     * Get sekolahAsal
     *
     * @return \Fast\SisdikBundle\Entity\SekolahAsal
     */
    public function getSekolahAsal() {
        return $this->sekolahAsal;
    }

    /**
     * Set orangtuaWali
     *
     * @param ArrayCollection $orangtuaWali
     */
    public function setOrangtuaWali(ArrayCollection $orangtuaWali) {
        foreach ($orangtuaWali as $data) {
            $data->setSiswa($this);
        }

        $this->orangtuaWali = $orangtuaWali;
    }

    /**
     * Get orangtuaWali
     *
     * @return \Fast\SisdikBundle\Entity\OrangtuaWali
     */
    public function getOrangtuaWali() {
        return $this->orangtuaWali;
    }

    /**
     * Set siswaKelas
     *
     * @param ArrayCollection $siswaKelas
     */
    public function setSiswaKelas(ArrayCollection $siswaKelas) {
        foreach ($siswaKelas as $data) {
            $data->setSiswa($this);
        }

        $this->siswaKelas = $siswaKelas;
    }

    /**
     * Get siswaKelas
     *
     * @return \Fast\SisdikBundle\Entity\SiswaKelas
     */
    public function getSiswaKelas() {
        return $this->siswaKelas;
    }

    /**
     * Get siswaKelas aktif
     *
     * @return array
     */
    public function getSiswaKelasAktif() {
        foreach ($this->getSiswaKelas() as $siswakelas) {
            if ($siswakelas->getTahunAkademik()->getAktif() === true) {
                return $siswakelas;
            }
        }

        return null;
    }

    /**
     * Get pembayaranPendaftaran
     *
     * @return \Fast\SisdikBundle\Entity\PembayaranPendaftaran
     */
    public function getPembayaranPendaftaran() {
        return $this->pembayaranPendaftaran;
    }

    /**
     * Get daftar biaya pendaftaran
     *
     * @return \Fast\SisdikBundle\Entity\DaftarBiayaPendaftaran
     * array of DaftarBiayaPendaftaran
     */
    public function getDaftarBiayaPendaftaran() {
        $daftar = array();

        foreach ($this->getPembayaranPendaftaran() as $pembayaran) {
            $daftar[] = $pembayaran->getDaftarBiayaPendaftaran();
        }

        return $daftar;
    }

    /**
     * Get transaksi pembayaran pendaftaran
     *
     * @return \Fast\SisdikBundle\Entity\TransaksiPembayaranPendaftaran
     * array of TransaksiPembayaranPendaftaran
     */
    public function getTransaksiPembayaranPendaftaran() {
        $daftar = array();

        foreach ($this->getPembayaranPendaftaran() as $pembayaran) {
            $daftar[] = $pembayaran->getTransaksiPembayaranPendaftaran();
        }

        return $daftar;
    }

    /**
     * Get total nominal biaya pendaftaran
     *
     * @return integer
     */
    public function getTotalNominalBiayaPendaftaran() {
        $jumlah = 0;

        foreach ($this->getPembayaranPendaftaran() as $pembayaran) {
            foreach ($pembayaran->getDaftarBiayaPendaftaran() as $daftar) {
                $jumlah += $daftar->getNominal();
            }
        }

        return $jumlah;
    }

    /**
     * Get total nominal pembayaran pendaftaran
     *
     * @return integer
     */
    public function getTotalNominalPembayaranPendaftaran() {
        $jumlah = 0;

        foreach ($this->getPembayaranPendaftaran() as $pembayaran) {
            foreach ($pembayaran->getTransaksiPembayaranPendaftaran() as $transaksi) {
                $jumlah += $transaksi->getNominalPembayaran();
            }
        }

        return $jumlah;
    }

    /**
     * Get total potongan pembayaran pendaftaran
     *
     * @return integer
     */
    public function getTotalPotonganPembayaranPendaftaran() {
        $jumlah = 0;

        foreach ($this->getPembayaranPendaftaran() as $pembayaran) {
            $jumlah += $pembayaran->getNominalPotongan() + $pembayaran->getPersenPotonganDinominalkan();
        }

        return $jumlah;
    }

    /**
     * Get pembayaranSekali
     *
     * @return \Fast\SisdikBundle\PembayaranSekali
     */
    public function getPembayaranSekali() {
        return $this->pembayaranSekali;

    }

    /**
     * Get total nominal pembayaran sekali bayar
     *
     * @return \Fast\SisdikBundle\PembayaranSekali
     */
    public function getTotalNominalPembayaranSekali() {
        $jumlah = 0;

        foreach ($this->getPembayaranSekali() as $pembayaran) {
            foreach ($pembayaran->getTransaksiPembayaranSekali() as $transaksi) {
                $jumlah += $transaksi->getNominalPembayaran();
            }
        }

        return $jumlah;
    }

    /**
     * Get pembayaranRutin
     *
     * @return \Fast\SisdikBundle\PembayaranRutin
     */
    public function getPembayaranRutin() {
        return $this->pembayaranRutin;
    }

    /**
     * Get total nominal pembayaran rutin
     *
     * @return \Fast\SisdikBundle\PembayaranRutin
     */
    public function getTotalNominalPembayaranRutin() {
        $jumlah = 0;

        foreach ($this->getPembayaranRutin() as $pembayaran) {
            foreach ($pembayaran->getTransaksiPembayaranRutin() as $transaksi) {
                $jumlah += $transaksi->getNominalPembayaran();
            }
        }

        return $jumlah;
    }

    public function getWebcamPhotoDir() {
        $fs = new Filesystem();
        if (!$fs->exists(self::WEBCAMPHOTO_DIR)) {
            $fs->mkdir(self::WEBCAMPHOTO_DIR);
        }
        return self::WEBCAMPHOTO_DIR;
    }

    public function getWebcamPhotoPath() {
        $fs = new Filesystem();
        if (!$fs
                ->exists(
                        self::WEBCAMPHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                                . $this->getTahun()->getTahun())) {
            $fs
                    ->mkdir(
                            self::WEBCAMPHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                                    . $this->getTahun()->getTahun());
        }

        return null === $this->fotoPendaftaran ? null
                : self::WEBCAMPHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                        . $this->getTahun()->getTahun() . DIRECTORY_SEPARATOR . $this->fotoPendaftaran;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile(UploadedFile $file) {
        $this->file = $file;

        return $this;
    }

    public function getWebPathFotoDisk() {
        return null === $this->fotoDisk ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR
                . $this->fotoDisk;
    }

    public function getRelativePathFotoDisk() {
        return null === $this->fotoDisk ? null
                : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->fotoDisk;
    }

    public function getRelativePathFotoDiskSebelumnya() {
        return null === $this->fotoDiskSebelumnya ? null
                : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->fotoDiskSebelumnya;
    }

    public function getRelativePathFotoDiskThumbSebelumnya() {
        return null === $this->fotoDiskSebelumnya ? null
                : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . self::THUMBNAIL_PREFIX
                        . $this->fotoDiskSebelumnya;
    }

    public function getFilesizeFotoDisk($type = 'KB') {
        $file = new File($this->getRelativePathFotoDisk());
        return FileSizeFormatter::formatBytes($file->getSize(), $type);
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->file) {
            $this->fotoDiskSebelumnya = $this->fotoDisk;

            $this->fotoDisk = sha1(uniqid(mt_rand(), true)) . '_' . $this->file->getClientOriginalName();
            $this->foto = $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        if ($this->file->move($this->getUploadRootDir(), $this->fotoDisk)) {

            $targetfile = $this->getAbsolutePath();
            $thumbnailfile = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . self::THUMBNAIL_PREFIX
                    . $this->fotoDisk;

            list($origWidth, $origHeight, $type, $attr) = @getimagesize($targetfile);
            if (is_numeric($type)) {

                $origRatio = $origWidth / $origHeight;
                $resultWidth = self::PHOTO_THUMB_WIDTH;
                $resultHeight = self::PHOTO_THUMB_HEIGHT;
                if ($resultWidth / $resultHeight > $origRatio) {
                    $resultWidth = $resultHeight * $origRatio;
                } else {
                    $resultHeight = $resultWidth / $origRatio;
                }

                // Set artificially high because GD uses uncompressed images in memory
                @ini_set('memory_limit', self::MEMORY_LIMIT);

                switch ($type) {
                    case IMAGETYPE_JPEG:
                        if (imagetypes() & IMG_JPEG) {
                            // resample image
                            $resultImage = imagecreatetruecolor($resultWidth, $resultHeight);

                            $srcImage = imagecreatefromjpeg($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagejpeg($resultImage, $thumbnailfile, 90);
                        }
                        break;
                    case IMAGETYPE_PNG:
                        if (imagetypes() & IMG_PNG) {
                            // resample image
                            // for png, we use imagecreate instead
                            $resultImage = imagecreate($resultWidth, $resultHeight);

                            $srcImage = imagecreatefrompng($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagepng($resultImage, $thumbnailfile, 8);
                        }
                        break;
                    case IMAGETYPE_GIF:
                        if (imagetypes() & IMG_GIF) {
                            // resample image
                            $resultImage = imagecreatetruecolor($resultWidth, $resultHeight);

                            $srcImage = imagecreatefromgif($targetfile);
                            imagecopyresampled($resultImage, $srcImage, 0, 0, 0, 0, $resultWidth,
                                    $resultHeight, $origWidth, $origHeight);

                            imagegif($resultImage, $thumbnailfile);
                        }
                        break;
                }
            }
        }

        $this->removeFotoSebelumnya();

        unset($this->file);
    }

    private function removeFotoSebelumnya() {
        if ($file = $this->getRelativePathFotoDiskSebelumnya()) {
            @unlink($file);
        }
        if ($fileThumb = $this->getRelativePathFotoDiskThumbSebelumnya()) {
            @unlink($fileThumb);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($file = $this->getAbsolutePath()) {
            @unlink($file);
        }
    }

    public function getAbsolutePath() {
        return null === $this->fotoDisk ? null
                : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->fotoDisk;
    }

    public function getWebPath() {
        return null === $this->fotoDisk ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR
                . $this->fotoDisk;
    }

    public function getWebPathThumbnail() {
        return null === $this->fotoDisk ? null
                : $this->getUploadDir() . DIRECTORY_SEPARATOR . self::THUMBNAIL_PREFIX . $this->fotoDisk;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        $fs = new Filesystem();
        if (!$fs
                ->exists(
                        self::PHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                                . $this->getTahun()->getTahun())) {
            $fs
                    ->mkdir(
                            self::PHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                                    . $this->getTahun()->getTahun());
        }
        return self::PHOTO_DIR . $this->getSekolah()->getId() . DIRECTORY_SEPARATOR
                . $this->getTahun()->getTahun();
    }
}
