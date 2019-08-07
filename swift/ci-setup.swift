#!/usr/bin/swift

import Foundation

// Promote SwiftLint's `todo` rule to `error` severity in a new config `.swiftlint-ci.yml`
let swiftLintConfigUrl = URL(fileURLWithPath: ".swiftlint.yml")
guard var swiftLintConfigContents = try String(bytes: Data(contentsOf: swiftLintConfigUrl), encoding: .utf8) else {
    fatalError("Failed to read file at path '\(swiftLintConfigUrl.relativeString)'.")
}
swiftLintConfigContents += """
todo:
  severity: error

"""
try swiftLintConfigContents.write(to: URL(fileURLWithPath: ".swiftlint-ci.yml"), atomically: false, encoding: .utf8)

// Ignore all frameworks when linting
let frameworksDirectoryPath = "./Frameworks"
if FileManager.default.fileExists(atPath: frameworksDirectoryPath) {
    // Find all sub directories
    let frameworksDirectoryUrl = URL(fileURLWithPath: frameworksDirectoryPath)
    let subDirectoryNames = try FileManager.default
        .contentsOfDirectory(atPath: frameworksDirectoryPath)
        .filter {
            var isDirectory = ObjCBool(false)
            let exists = FileManager.default.fileExists(
                atPath: frameworksDirectoryUrl.appendingPathComponent($0).relativeString,
                isDirectory: &isDirectory
            )

            return exists && isDirectory.boolValue
        }

    // Add a `.swiftlint.yml` to the frameworks directory to exclude all sub directories
    let excludeList = subDirectoryNames
        .map { "  - '\($0)'" }
        .joined(separator: "\n")
    let configContents = "excluded:\n\(excludeList)\n"
    try configContents.write(
        to: URL(fileURLWithPath: frameworksDirectoryPath).appendingPathComponent(".swiftlint.yml"),
        atomically: false,
        encoding: .utf8
    )
}
