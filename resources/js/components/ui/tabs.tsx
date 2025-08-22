"use client"

import * as React from "react"
import { cn } from "@/lib/utils"

// Minimal dependency-free Tabs components compatible with basic Radix-like API.

type TabsContextValue = {
  value: string | undefined
  setValue: (v: string) => void
}

const TabsContext = React.createContext<TabsContextValue | null>(null)

type TabsProps = React.HTMLAttributes<HTMLDivElement> & {
  value?: string
  defaultValue?: string
  onValueChange?: (value: string) => void
}

const Tabs = ({ value, defaultValue, onValueChange, className, children, ...props }: TabsProps) => {
  const [internal, setInternal] = React.useState<string | undefined>(defaultValue)
  const isControlled = value !== undefined
  const current = isControlled ? value : internal

  const setValue = React.useCallback(
    (v: string) => {
      onValueChange?.(v)
      if (!isControlled) setInternal(v)
    },
    [isControlled, onValueChange]
  )

  const ctx = React.useMemo(() => ({ value: current, setValue }), [current, setValue])

  return (
    <TabsContext.Provider value={ctx}>
      <div className={cn(className)} {...props}>{children}</div>
    </TabsContext.Provider>
  )
}

const TabsList = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn(
        "inline-flex h-10 items-center justify-center rounded-md bg-slate-100 p-1 text-slate-500 dark:bg-slate-800 dark:text-slate-400",
        className
      )}
      {...props}
    />
  )
)
TabsList.displayName = "TabsList"

type TabsTriggerProps = React.ButtonHTMLAttributes<HTMLButtonElement> & { value: string }

const TabsTrigger = React.forwardRef<HTMLButtonElement, TabsTriggerProps>(({ className, value, onClick, ...props }, ref) => {
  const ctx = React.useContext(TabsContext)
  const active = ctx?.value === value
  return (
    <button
      ref={ref}
      type="button"
      data-state={active ? "active" : "inactive"}
      className={cn(
        "inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-white transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-white data-[state=active]:text-slate-950 data-[state=active]:shadow-sm dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 dark:data-[state=active]:bg-slate-950 dark:data-[state=active]:text-slate-50",
        className
      )}
      onClick={(e) => {
        onClick?.(e)
        ctx?.setValue(value)
      }}
      {...props}
    />
  )
})
TabsTrigger.displayName = "TabsTrigger"

type TabsContentProps = React.HTMLAttributes<HTMLDivElement> & { value: string }

const TabsContent = React.forwardRef<HTMLDivElement, TabsContentProps>(({ className, value, hidden, style, ...props }, ref) => {
  const ctx = React.useContext(TabsContext)
  const active = ctx?.value === value
  return (
    <div
      ref={ref}
      data-state={active ? "active" : "inactive"}
      className={cn(
        "mt-2 ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300",
        !active && "hidden",
        className
      )}
      style={style}
      hidden={hidden}
      {...props}
    />
  )
})
TabsContent.displayName = "TabsContent"

export { Tabs, TabsList, TabsTrigger, TabsContent }
