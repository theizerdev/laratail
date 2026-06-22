import React, { useRef, useState, useCallback } from 'react';
import Webcam from 'react-webcam';
import { Button } from '@/components/ui/button';
import { Camera, RefreshCw } from 'lucide-react';

interface WebcamCaptureProps {
  onCapture: (imageSrc: string) => void;
  currentPhoto?: string | null;
}

export function WebcamCapture({ onCapture, currentPhoto }: WebcamCaptureProps) {
  const webcamRef = useRef<Webcam>(null);
  const [imgSrc, setImgSrc] = useState<string | null>(currentPhoto || null);
  const [isCapturing, setIsCapturing] = useState(!currentPhoto);

  const capture = useCallback(() => {
    if (webcamRef.current) {
      const imageSrc = webcamRef.current.getScreenshot();
      if (imageSrc) {
        setImgSrc(imageSrc);
        setIsCapturing(false);
        onCapture(imageSrc);
      }
    }
  }, [webcamRef, onCapture]);

  const retake = () => {
    setImgSrc(null);
    setIsCapturing(true);
    onCapture(''); // Clear the photo
  };

  return (
    <div className="flex flex-col items-center gap-4">
      {isCapturing ? (
        <div className="relative w-full max-w-sm overflow-hidden rounded-lg bg-black/5 aspect-square flex items-center justify-center">
          <Webcam
            audio={false}
            ref={webcamRef}
            screenshotFormat="image/jpeg"
            videoConstraints={{
              width: 400,
              height: 400,
              facingMode: 'user',
            }}
            className="object-cover w-full h-full"
          />
          <Button
            type="button"
            onClick={capture}
            className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full shadow-lg"
          >
            <Camera className="mr-2 size-4" /> Capturar Foto
          </Button>
        </div>
      ) : (
        <div className="relative w-full max-w-sm rounded-lg overflow-hidden border aspect-square flex items-center justify-center bg-gray-50">
          {imgSrc && <img src={imgSrc} alt="Fotografía" className="object-cover w-full h-full" />}
          <Button
            type="button"
            variant="secondary"
            onClick={retake}
            className="absolute bottom-4 left-1/2 -translate-x-1/2 shadow-lg"
          >
            <RefreshCw className="mr-2 size-4" /> Tomar otra
          </Button>
        </div>
      )}
    </div>
  );
}
