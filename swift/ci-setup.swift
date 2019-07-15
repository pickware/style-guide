#!/usr/bin/swift

import Foundation

/// Copys the content of a given file to a new file at a given path
///
/// - Parameters:
///     - path1: the path of the file to copy from
///     - path2: the path of the file to copy to
func copy(fromPath path1: String, toPath path2: String) {
    guard let fileContent = FileManager.default.contents(atPath: path1) else {
        print("Could not extract content at \(path1)")

        return
    }
    FileManager.default.createFile(atPath: path2, contents: fileContent, attributes: nil)
}

/// Adds a given String at the end of a given file.
///
/// - Parameters:
///     - content: the String that should be added to the end of the file
///     - path: the path of the file that `content` should be appended to
/// - Throws: If writing the adjusted content back to the file fails.
func add(content: String, toPath path: String) throws {
    let fileUpdater = FileHandle(forWritingAtPath: path)!
    fileUpdater.seekToEndOfFile()
    fileUpdater.write(content.data(using: .utf8)!)
    fileUpdater.closeFile()
}

/// Appends a given keyword to a given file and reappends all of the file's lines after this keyword preceded by ' - '
///
/// - Parameters:
///     - keyword: the String that is first appended to the file
///     - path: path of the file that the keyword should be appended to
/// - Throws: If adding a String to the end of the file fails.
func appendAllFiles(at path1: String, after keyword: String, to path2: String) throws {
    if FileManager.default.fileExists(atPath: path1) {
        try add(content: keyword, toPath: path2)
        guard let files = try? FileManager.default.contentsOfDirectory(atPath: path1) else {
            return
        }

        let stringToAppend = files
            .map { file in
                "\n - \(file) \n"
            }
            .reduce("", +)
        try add(content: stringToAppend, toPath: path2)
    } else {
        print("\(path1) does not exist")
    }
}

// Setup file paths
let swiftlintConfigPath = ".swiftlint.yml"
let ciSwiftlintConfigPath = ".swiftlint-ci.yml"

// Promote SwiftLint's `todo` rule to `error` severity
copy(fromPath: swiftlintConfigPath, toPath: ciSwiftlintConfigPath)

let ciConfigAddition = """

todo:
severity: error
"""

try add(content: ciConfigAddition, toPath: ciSwiftlintConfigPath)

// Ignore all frameworks when linting
try appendAllFiles(at: "Frameworks/", after: "\n exclude: \n", to: swiftlintConfigPath)
