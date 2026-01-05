import React, { useEffect, useRef, useState } from 'react';

interface AdSenseProps {
  slot?: string;
  format?: 'auto' | 'fluid' | 'rectangle';
  responsive?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

const AdSense: React.FC<AdSenseProps> = ({
  slot = 'auto',
  format = 'auto',
  responsive = true,
  className = '',
  style = { display: 'block' },
}) => {
  const adRef = useRef<HTMLDivElement>(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const attemptsRef = useRef(0);
  const maxAttempts = 50;

  useEffect(() => {
    let timeoutId: NodeJS.Timeout;

    const loadAd = () => {
      if (!adRef.current || isLoaded) return;
      
      attemptsRef.current += 1;
      const width = adRef.current.offsetWidth;
      
      if (width === 0 && attemptsRef.current < maxAttempts) {
        timeoutId = setTimeout(loadAd, 100);
        return;
      }

      if (width === 0) {
        return;
      }

      try {
        // @ts-ignore
        (window.adsbygoogle = window.adsbygoogle || []).push({});
        setIsLoaded(true);
      } catch (e) {
        // Silently fail - AdSense errors are expected during development
      }
    };

    timeoutId = setTimeout(loadAd, 200);
    
    return () => {
      if (timeoutId) clearTimeout(timeoutId);
    };
  }, [isLoaded]);

  return (
    <div ref={adRef} className={`adsense-container my-6 md:my-8 flex justify-center w-full overflow-hidden ${className}`}>
      <ins
        className="adsbygoogle"
        style={{ 
          ...style, 
          minHeight: '50px',
          maxHeight: '250px',
          width: '100%',
          maxWidth: '100%'
        }}
        data-ad-client="ca-pub-1406842788891515"
        data-ad-slot={slot}
        data-ad-format={format}
        data-full-width-responsive={responsive ? 'true' : 'false'}
      />
    </div>
  );
};

export default AdSense;
