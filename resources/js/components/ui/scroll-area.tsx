"use client"

import * as React from "react"
import { cn } from "@/lib/utils"

// Minimal dependency-free ScrollArea implementation to avoid requiring
// @radix-ui/react-scroll-area during SSR/type-check.

type DivProps = React.HTMLAttributes<HTMLDivElement>

const ScrollArea = React.forwardRef<HTMLDivElement, DivProps>(
  ({ className, children, ...props }, ref) => (
    <div ref={ref} className={cn("relative overflow-auto", className)} {...props}>
      <div className="h-full w-full rounded-[inherit]">
        {children}
      </div>
    </div>
  )
)
ScrollArea.displayName = "ScrollArea"

const ScrollBar = React.forwardRef<HTMLDivElement, DivProps & { orientation?: "vertical" | "horizontal" }>(
  ({ className, orientation = "vertical", ...props }, ref) => (
    <div
      ref={ref}
      data-orientation={orientation}
      className={cn(
        "pointer-events-none opacity-0", // decorative placeholder
        className
      )}
      {...props}
    />
  )
)
ScrollBar.displayName = "ScrollBar"

export { ScrollArea, ScrollBar }
