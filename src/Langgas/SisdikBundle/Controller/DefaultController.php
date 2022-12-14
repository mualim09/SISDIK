<?php

namespace Langgas\SisdikBundle\Controller;

use Doctrine\ORM\EntityManager;
use Langgas\SisdikBundle\Entity\User;
use Langgas\SisdikBundle\Entity\PanitiaPendaftaran;
use Langgas\SisdikBundle\Entity\TahunAkademik;
use Langgas\SisdikBundle\Entity\Sekolah;
use Langgas\SisdikBundle\Entity\KehadiranSiswa;
use Langgas\SisdikBundle\Entity\Tingkat;
use Langgas\SisdikBundle\Entity\JadwalKehadiran;
use Langgas\SisdikBundle\Entity\JadwalKepulangan;
use Langgas\SisdikBundle\Entity\Kelas;
use Langgas\SisdikBundle\Entity\KalenderPendidikan;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\Expression;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $authorizationChecker = $this->container->get('security.authorization_checker');

        if ($authorizationChecker->isGranted([new Expression('hasRole("ROLE_SISWA") and not hasAnyRole("ROLE_SUPER_ADMIN", "ROLE_WALI_KELAS")')])) {
            return $this->redirect($this->generateUrl('siswa__kehadiran'));
        } elseif ($authorizationChecker->isGranted([new Expression('hasRole("ROLE_SUPER_ADMIN")')])) {
            $response = $this->forward('LanggasSisdikBundle:Default:super');
        } else {
            $response = $this->forward('LanggasSisdikBundle:Default:pengelola');
        }

        return $response;
    }

    /**
     * @Template()
     */
    public function superAction()
    {

    }

    /**
     * @Template()
     */
    public function pengelolaAction()
    {
        $sekolah = $this->getSekolah();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $tahunAkademikAktif = $em->getRepository('LanggasSisdikBundle:TahunAkademik')
            ->findOneBy([
                'sekolah' => $sekolah,
                'aktif' => true,
            ])
        ;

        $panitiaPendaftaranAktif = $em->getRepository('LanggasSisdikBundle:PanitiaPendaftaran')
            ->findOneBy([
                'sekolah' => $sekolah,
                'aktif' => true,
            ])
        ;

        $personilPanitiaPendaftaranAktif = null;
        if (is_object($panitiaPendaftaranAktif) && $panitiaPendaftaranAktif instanceof PanitiaPendaftaran) {
            $ketuaPanitiaPendaftaranAktif = $panitiaPendaftaranAktif->getKetuaPanitia()->getName();

            $tempArray = [];
            foreach ($panitiaPendaftaranAktif->getPanitia() as $personil) {
                $entity = $em->getRepository('LanggasSisdikBundle:User')->find($personil);

                if ($entity instanceof User) {
                    $tempArray[] = $entity->getName();
                } else {
                    $tempArray[] = $this->get('translator')->trans('label.username.undefined');
                }
            }
            $personilPanitiaPendaftaranAktif = implode(", ", $tempArray);
        }

        $tanggalTampil = new \DateTime();
        $tanggalSebelumnya = $tanggalTampil->modify('-1 day')->format('Y-m-d');
        $tanggalBerikutnya = $tanggalTampil->modify('+2 day')->format('Y-m-d');
        $tanggalTampil->modify('-1 day');

        $daftarStatusKehadiran = JadwalKehadiran::getDaftarStatusKehadiran();
        $daftarStatusKepulangan = JadwalKepulangan::getDaftarStatusKepulangan();

        $daftarTingkat = $em->getRepository('LanggasSisdikBundle:Tingkat')
            ->findBy([
                'sekolah' => $sekolah,
            ], [
                'nama' => 'ASC',
                'kode' => 'ASC',
                'urutan' => 'ASC',
            ])
        ;

        $kalenderPendidikan = $em->getRepository('LanggasSisdikBundle:KalenderPendidikan')
            ->findOneBy([
                'sekolah' => $sekolah,
                'tanggal' => $tanggalTampil,
                'kbm' => true,
            ])
        ;

        $kehadiranSiswaTotal = null;
        $kepulanganSiswaTotal = null;
        $kehadiran = [];
        $kepulangan = [];
        if (is_object($kalenderPendidikan) && $kalenderPendidikan instanceof KalenderPendidikan) {
            foreach ($daftarStatusKehadiran as $key => $val) {
                $result = $em->createQueryBuilder()
                    ->select('COUNT(kehadiranSiswa.id)')
                    ->from('LanggasSisdikBundle:KehadiranSiswa', 'kehadiranSiswa')
                    ->where('kehadiranSiswa.sekolah = :sekolah')
                    ->andWhere('kehadiranSiswa.tahunAkademik = :tahunAkademik')
                    ->andWhere('kehadiranSiswa.tanggal = :tanggal')
                    ->andWhere('kehadiranSiswa.statusKehadiran = :statusKehadiran')
                    ->setParameter('sekolah', $sekolah)
                    ->setParameter('tahunAkademik', $tahunAkademikAktif)
                    ->setParameter('tanggal', $tanggalTampil->format("Y-m-d"))
                    ->setParameter('statusKehadiran', $key)
                    ->getQuery()
                    ->getSingleScalarResult()
                ;
                $kehadiran[$key] = $result;
            }

            foreach ($daftarStatusKepulangan as $key => $val) {
                $result = $em->createQueryBuilder()
                    ->select('COUNT(kepulanganSiswa.id)')
                    ->from('LanggasSisdikBundle:KepulanganSiswa', 'kepulanganSiswa')
                    ->where('kepulanganSiswa.sekolah = :sekolah')
                    ->andWhere('kepulanganSiswa.tahunAkademik = :tahunAkademik')
                    ->andWhere('kepulanganSiswa.tanggal = :tanggal')
                    ->andWhere('kepulanganSiswa.statusKepulangan = :statusKepulangan')
                    ->setParameter('sekolah', $sekolah)
                    ->setParameter('tahunAkademik', $tahunAkademikAktif)
                    ->setParameter('tanggal', $tanggalTampil->format("Y-m-d"))
                    ->setParameter('statusKepulangan', $key)
                    ->getQuery()
                    ->getSingleScalarResult()
                ;
                $kepulangan[$key] = $result;
            }
        } else {
            foreach ($daftarStatusKehadiran as $key => $val) {
                $kehadiran[$key] = 0;
            }

            foreach ($daftarStatusKepulangan as $key => $val) {
                $kepulangan[$key] = 0;
            }
        }
        $kehadiranSiswaTotal = $kehadiran;
        $kepulanganSiswaTotal = $kepulangan;

        $daftarKelasPerTingkat = [];
        $kehadiranSiswaPerKelas = [];
        $kepulanganSiswaPerKelas = [];
        foreach ($daftarTingkat as $tingkat) {
            $daftarKelas = $em->getRepository('LanggasSisdikBundle:Kelas')
                ->findBy([
                    'sekolah' => $sekolah,
                    'tahunAkademik' => $tahunAkademikAktif,
                    'tingkat' => $tingkat,
                ], [
                    'nama' => 'ASC',
                    'kode' => 'ASC',
                    'urutan' => 'ASC',
                ])
            ;

            $daftarKelasPerTingkat[$tingkat->getId()] = $daftarKelas;

            foreach ($daftarKelas as $kelas) {
                if ($kelas instanceof Kelas) {
                    $kehadiran = [];
                    $kepulangan = [];
                    if (is_object($kalenderPendidikan) && $kalenderPendidikan instanceof KalenderPendidikan) {
                        foreach ($daftarStatusKehadiran as $key => $val) {
                            $result = $em->createQueryBuilder()
                                ->select('COUNT(kehadiranSiswa.id)')
                                ->from('LanggasSisdikBundle:KehadiranSiswa', 'kehadiranSiswa')
                                ->where('kehadiranSiswa.sekolah = :sekolah')
                                ->andWhere('kehadiranSiswa.tahunAkademik = :tahunAkademik')
                                ->andWhere('kehadiranSiswa.kelas = :kelas')
                                ->andWhere('kehadiranSiswa.tanggal = :tanggal')
                                ->andWhere('kehadiranSiswa.statusKehadiran = :statusKehadiran')
                                ->setParameter('sekolah', $sekolah)
                                ->setParameter('tahunAkademik', $tahunAkademikAktif)
                                ->setParameter('kelas', $kelas)
                                ->setParameter('tanggal', $tanggalTampil->format("Y-m-d"))
                                ->setParameter('statusKehadiran', $key)
                                ->getQuery()
                                ->getSingleScalarResult()
                            ;
                            $kehadiran[$key] = $result;
                        }

                        foreach ($daftarStatusKepulangan as $key => $val) {
                            $result = $em->createQueryBuilder()
                                ->select('COUNT(kepulanganSiswa.id)')
                                ->from('LanggasSisdikBundle:KepulanganSiswa', 'kepulanganSiswa')
                                ->where('kepulanganSiswa.sekolah = :sekolah')
                                ->andWhere('kepulanganSiswa.tahunAkademik = :tahunAkademik')
                                ->andWhere('kepulanganSiswa.kelas = :kelas')
                                ->andWhere('kepulanganSiswa.tanggal = :tanggal')
                                ->andWhere('kepulanganSiswa.statusKepulangan = :statusKepulangan')
                                ->setParameter('sekolah', $sekolah)
                                ->setParameter('tahunAkademik', $tahunAkademikAktif)
                                ->setParameter('kelas', $kelas)
                                ->setParameter('tanggal', $tanggalTampil->format("Y-m-d"))
                                ->setParameter('statusKepulangan', $key)
                                ->getQuery()
                                ->getSingleScalarResult()
                            ;
                            $kepulangan[$key] = $result;
                        }
                    } else {
                        foreach ($daftarStatusKehadiran as $key => $val) {
                            $kehadiran[$key] = 0;
                        }

                        foreach ($daftarStatusKepulangan as $key => $val) {
                            $kepulangan[$key] = 0;
                        }
                    }
                    $kehadiranSiswaPerKelas[$kelas->getId()] = $kehadiran;
                    $kepulanganSiswaPerKelas[$kelas->getId()] = $kepulangan;
                }
            }
        }

        $searchform = $this->createForm('sisdik_kehadiransiswasearch');
        $searchform->setData(['tanggal' => $tanggalTampil]);

        return [
            'tahunAkademikAktif' => $tahunAkademikAktif,
            'panitiaPendaftaranAktif' => $panitiaPendaftaranAktif,
            'personilPanitiaPendaftaranAktif' => $personilPanitiaPendaftaranAktif,
            'tanggalTampil' => $tanggalTampil,
            'tanggalSebelumnya' => $tanggalSebelumnya,
            'tanggalBerikutnya' => $tanggalBerikutnya,
            'daftarStatusKehadiran' => $daftarStatusKehadiran,
            'daftarStatusKepulangan' => $daftarStatusKepulangan,
            'daftarTingkat' => $daftarTingkat,
            'daftarKelasPerTingkat' => $daftarKelasPerTingkat,
            'kehadiranSiswaTotal' => $kehadiranSiswaTotal,
            'kepulanganSiswaTotal' => $kepulanganSiswaTotal,
            'kehadiranSiswaPerKelas' => $kehadiranSiswaPerKelas,
            'kepulanganSiswaPerKelas' => $kepulanganSiswaPerKelas,
            'searchform' => $searchform->createView(),
        ];
    }

    /**
     * @return Sekolah
     */
    private function getSekolah()
    {
        return $this->getUser()->getSekolah();
    }
}
